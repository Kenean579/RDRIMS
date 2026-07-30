<?php

namespace App\Services;

use App\Models\Publication;
use App\Models\PublicationHistory;
use App\Models\PublicationStatus;
use Illuminate\Support\Facades\DB;

class PublicationService
{
    /**
     * Create a new publication
     */
    public function create(array $data, int $userId): Publication
    {
        return DB::transaction(function () use ($data, $userId) {
            $data['created_by'] = $userId;
            $data['updated_by'] = $userId;
            
            // Get draft status
            $draftStatus = PublicationStatus::where('name', 'draft')->first();
            if (!$draftStatus) {
                throw new \Exception('Draft status not found. Please seed publication statuses.');
            }
            
            // Temporarily unguard to set status_id
            Publication::unguard();
            $data['status_id'] = $draftStatus->id;
            $publication = Publication::create($data);
            Publication::reguard();

            // Log creation
            $this->logHistory(
                $publication,
                'created',
                $userId,
                "Publication '{$publication->title}' created"
            );

            return $publication->fresh();
        });
    }

    /**
     * Update a publication
     */
    public function update(Publication $publication, array $data, int $userId): Publication
    {
        return DB::transaction(function () use ($publication, $data, $userId) {
            $oldData = $publication->only(['title', 'abstract', 'journal', 'doi', 'publication_date']);
            $data['updated_by'] = $userId;
            
            $publication->update($data);

            // Log update
            $this->logHistory(
                $publication,
                'updated',
                $userId,
                "Publication '{$publication->title}' updated",
                ['old' => $oldData, 'new' => $data]
            );

            return $publication;
        });
    }

    /**
     * Submit publication for review
     */
    public function submit(Publication $publication, int $userId): Publication
    {
        return DB::transaction(function () use ($publication, $userId) {
            // Validation: must be in draft status
            if ($publication->status?->name !== 'draft') {
                throw new \InvalidArgumentException('Only draft publications can be submitted');
            }

            // Validation: must have at least one internal author
            if (!$publication->hasInternalAuthor()) {
                throw new \InvalidArgumentException('Publication must have at least one internal author');
            }

            $submittedStatus = PublicationStatus::where('name', 'submitted')->first();
            
            Publication::unguard();
            $publication->update([
                'status_id' => $submittedStatus->id,
                'updated_by' => $userId,
            ]);
            Publication::reguard();

            $this->logHistory(
                $publication,
                'submitted',
                $userId,
                "Publication '{$publication->title}' submitted for review"
            );

            return $publication->fresh();
        });
    }

    /**
     * Verify a publication (admin action)
     */
    public function verify(Publication $publication, int $userId): Publication
    {
        return DB::transaction(function () use ($publication, $userId) {
            Publication::unguard();
            $publication->update([
                'verified_by' => $userId,
                'verified_at' => now(),
                'updated_by' => $userId,
            ]);
            Publication::reguard();

            $this->logHistory(
                $publication,
                'verified',
                $userId,
                "Publication '{$publication->title}' verified"
            );

            return $publication->fresh();
        });
    }

    /**
     * Approve a publication
     */
    public function approve(Publication $publication, int $userId, ?string $comments = null): Publication
    {
        return DB::transaction(function () use ($publication, $userId, $comments) {
            // Validation: should be submitted or under review
            if (!in_array($publication->status?->name, ['submitted', 'under_review'])) {
                throw new \InvalidArgumentException('Only submitted or under review publications can be approved');
            }

            $acceptedStatus = PublicationStatus::where('name', 'accepted')->first();
            
            Publication::unguard();
            $publication->update([
                'status_id' => $acceptedStatus->id,
                'updated_by' => $userId,
            ]);
            Publication::reguard();

            $description = "Publication '{$publication->title}' approved";
            if ($comments) {
                $description .= ": $comments";
            }

            $this->logHistory($publication, 'approved', $userId, $description);

            return $publication->fresh();
        });
    }

    /**
     * Reject a publication
     */
    public function reject(Publication $publication, int $userId, string $reason): Publication
    {
        return DB::transaction(function () use ($publication, $userId, $reason) {
            $rejectedStatus = PublicationStatus::where('name', 'rejected')->first();
            
            Publication::unguard();
            $publication->update([
                'status_id' => $rejectedStatus->id,
                'updated_by' => $userId,
            ]);
            Publication::reguard();

            $this->logHistory(
                $publication,
                'rejected',
                $userId,
                "Publication '{$publication->title}' rejected: $reason"
            );

            return $publication->fresh();
        });
    }

    /**
     * Publish a publication
     */
    public function publish(Publication $publication, int $userId): Publication
    {
        return DB::transaction(function () use ($publication, $userId) {
            // Validation: must be accepted
            if ($publication->status?->name !== 'accepted') {
                throw new \InvalidArgumentException('Only accepted publications can be published');
            }

            // Validation: must be verified
            if (!$publication->isVerified()) {
                throw new \InvalidArgumentException('Publication must be verified before publishing');
            }

            $publishedStatus = PublicationStatus::where('name', 'published')->first();
            
            Publication::unguard();
            $publication->update([
                'status_id' => $publishedStatus->id,
                'updated_by' => $userId,
            ]);
            Publication::reguard();

            $this->logHistory(
                $publication,
                'published',
                $userId,
                "Publication '{$publication->title}' published"
            );

            return $publication->fresh();
        });
    }

    /**
     * Add an author to the publication
     */
    public function addAuthor(Publication $publication, array $data, int $performedBy): void
    {
        DB::transaction(function () use ($publication, $data, $performedBy) {
            // Check if author already exists
            if (isset($data['user_id'])) {
                $exists = $publication->authors()
                    ->where('user_id', $data['user_id'])
                    ->exists();
                    
                if ($exists) {
                    throw new \InvalidArgumentException('This user is already an author on this publication');
                }
            }

            $author = $publication->authors()->create($data);

            $authorName = $author->user?->name ?? $author->external_author_name;
            $this->logHistory(
                $publication,
                'author_added',
                $performedBy,
                "Author '{$authorName}' added to publication"
            );
        });
    }

    /**
     * Remove an author from the publication
     */
    public function removeAuthor(Publication $publication, int $authorId, int $performedBy): void
    {
        DB::transaction(function () use ($publication, $authorId, $performedBy) {
            $author = $publication->authors()->findOrFail($authorId);
            $authorName = $author->user?->name ?? $author->external_author_name;

            // Prevent removing last internal author
            if ($author->user_id && $publication->authors()->whereNotNull('user_id')->count() === 1) {
                throw new \InvalidArgumentException('Cannot remove the last internal author');
            }

            $author->delete();

            $this->logHistory(
                $publication,
                'author_removed',
                $performedBy,
                "Author '{$authorName}' removed from publication"
            );
        });
    }

    /**
     * Update citation count
     */
    public function updateCitations(Publication $publication, int $citationCount, int $userId): Publication
    {
        return DB::transaction(function () use ($publication, $citationCount, $userId) {
            $oldCount = $publication->citation_count;
            
            $publication->update([
                'citation_count' => $citationCount,
                'updated_by' => $userId,
            ]);

            $this->logHistory(
                $publication,
                'citations_updated',
                $userId,
                "Citation count updated from {$oldCount} to {$citationCount}"
            );

            return $publication;
        });
    }

    /**
     * Log history entry
     */
    private function logHistory(Publication $publication, string $action, int $userId, string $description, ?array $changes = null): void
    {
        PublicationHistory::create([
            'publication_id' => $publication->id,
            'action' => $action,
            'performed_by' => $userId,
            'description' => $description,
            'changes' => $changes,
        ]);
    }

    /**
     * Get publication statistics
     */
    public function getStatistics(int $universityId = null): array
    {
        $query = Publication::query();

        if ($universityId) {
            $query->forUniversity($universityId);
        }

        $totalPublications = $query->count();
        $publishedPublications = (clone $query)->published()->count();
        $totalCitations = (clone $query)->sum('citation_count');
        
        $byType = (clone $query)
            ->with('type')
            ->get()
            ->groupBy('type.name')
            ->map->count();

        $byYear = (clone $query)
            ->selectRaw('YEAR(publication_date) as year, COUNT(*) as count')
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->limit(10)
            ->pluck('count', 'year');

        return [
            'total_publications' => $totalPublications,
            'published_publications' => $publishedPublications,
            'total_citations' => $totalCitations,
            'average_citations' => $totalPublications > 0 ? round($totalCitations / $totalPublications, 2) : 0,
            'by_type' => $byType,
            'by_year' => $byYear,
        ];
    }
}
