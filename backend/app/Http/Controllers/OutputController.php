<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangeOutputStatusRequest;
use App\Http\Requests\StoreOutputRequest;
use App\Http\Requests\UpdateOutputRequest;
use App\Models\Output;
use App\Services\OutputService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OutputController extends Controller
{
    public function __construct(
        private OutputService $outputService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();

        $query = Output::with(['category', 'status', 'subtype', 'participants'])
            ->when($request->status, fn($q) => $q->whereHas('status', fn($s) => $s->where('name', $request->status)))
            ->when($request->category, fn($q) => $q->whereHas('category', fn($c) => $c->where('name', $request->category)))
            ->when($request->student_level, fn($q) => $q->whereHas('studentLevel', fn($sl) => $sl->where('name', $request->student_level)))
            ->when($request->subtype, fn($q) => $q->whereHas('subtype', fn($st) => $st->where('name', $request->subtype)))
            ->when($request->search, fn($q) => $q->where('title', 'LIKE', '%' . $request->search . '%'));

        // Tenant isolation: scope by the participants' institutional hierarchy.
        if ($user && !$user->hasRole('super_admin')) {
            if ($user->hasRole('student')) {
                // Students see only their own outputs
                $query->whereHas('participants', function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->whereHas('participantType', fn($pt) => $pt->where('name', 'student'));
                });
            } else {
                // All other roles: scope via participant user's institutional hierarchy
                $query->whereHas('participants', function ($q) use ($user) {
                    $q->hierarchical($user, 'id');
                });
            }
        }

        $outputs = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($outputs);
    }

    /**
     * Public endpoint for viewing approved student outputs
     */
    public function publicIndex(Request $request): JsonResponse
    {
        $outputs = Output::with(['category', 'studentLevel', 'subtype', 'participants'])
            ->whereHas('status', fn($s) => $s->where('name', 'approved'))
            ->when($request->category, fn($q) => $q->whereHas('category', fn($c) => $c->where('name', $request->category)))
            ->when($request->student_level, fn($q) => $q->whereHas('studentLevel', fn($sl) => $sl->where('name', $request->student_level)))
            ->when($request->subtype, fn($q) => $q->whereHas('subtype', fn($st) => $st->where('name', $request->subtype)))
            ->when($request->search, fn($q) => $q->where('title', 'LIKE', '%' . $request->search . '%'))
            ->when($request->academic_year, fn($q) => $q->whereHas('academicYear', fn($ay) => $ay->where('name', $request->academic_year)))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($outputs);
    }

    public function store(StoreOutputRequest $request): JsonResponse
    {
        return \DB::transaction(function() use ($request) {
            $data = $request->validated();
            $participants = $data['participants'] ?? [];
            unset($data['participants']);

            // Use the service layer to create output with audit fields
            $output = $this->outputService->create($data, auth()->user()->id);

            // Auto-add student as participant for student outputs
            if ($output->category->name === 'student' && auth()->check()) {
                $this->outputService->addStudentParticipant($output, auth()->id());
            }

            // Add other participants
            foreach ($participants as $p) {
                if ($p['user_id']) {
                    $output->participants()->attach($p['user_id'], ['participant_type_id' => $p['participant_type_id']]);
                }
            }

            // Validate supervisor assignment for student outputs
            if ($output->category->name === 'student') {
                if (!$this->outputService->validateStudentOutputParticipants($output)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'participants' => 'Student outputs must have at least one supervisor assigned.'
                    ]);
                }
            }

            return response()->json($output->load('category', 'status', 'participants'), 201);
        });
    }

    public function show(Output $output): JsonResponse
    {
        $this->authorize('view', $output);
        return response()->json($output->load('category', 'status', 'participants', 'files', 'project'));
    }

    public function update(UpdateOutputRequest $request, Output $output): JsonResponse
    {
        $this->authorize('update', $output);
        $output->update($request->validated());
        return response()->json($output);
    }

    public function destroy(Output $output): JsonResponse
    {
        $this->authorize('delete', $output);
        $output->delete();
        return response()->json(['message' => 'Output deleted.']);
    }

    public function submit(Output $output): JsonResponse
    {
        $this->authorize('submit', $output);
        
        try {
            $output = $this->outputService->submit($output, auth()->user()->id);
            return response()->json($output->load('status'));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function verify(Output $output): JsonResponse
    {
        $this->authorize('verify', $output);
        
        $output = $this->outputService->verify($output, auth()->user()->id);
        return response()->json($output->load('verifiedBy'));
    }

    public function approve(Output $output): JsonResponse
    {
        $this->authorize('approve', $output);
        
        try {
            $output = $this->outputService->approve($output, auth()->user()->id);
            return response()->json($output->load('status'));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function reject(Request $request, Output $output): JsonResponse
    {
        $this->authorize('reject', $output);
        
        $request->validate(['reason' => 'nullable|string|max:1000']);
        
        try {
            $output = $this->outputService->reject($output, auth()->user()->id, $request->reason);
            return response()->json($output->load('status'));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function publish(Output $output): JsonResponse
    {
        $this->authorize('publish', $output);
        
        try {
            $output = $this->outputService->publish($output, auth()->user()->id);
            return response()->json($output->load('status'));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function getSubtypesByLevel(Request $request): JsonResponse
    {
        $studentLevelId = $request->query('student_level_id');
        $subtypes = $this->outputService->getSubtypesByLevel($studentLevelId);
        return response()->json($subtypes);
    }
}