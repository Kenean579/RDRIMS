<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Send a notification to a user through all enabled channels.
     *
     * @param User   $user    The recipient.
     * @param string $type    Notification type identifier (e.g. 'proposal_submitted', 'review_assigned').
     * @param string $message Human-readable message.
     * @param array  $context Optional data for email template rendering (e.g. ['proposal_title' => '...', 'link' => '...']).
     */
    public function notify(User $user, string $type, string $message, array $context = [], string $priority = 'Informational'): void
    {
        // 1. In-app notification (always)
        $this->createInApp($user, $type, $message, $context, $priority);

        // 2. Email notification (if enabled globally and user has email)
        if ($this->shouldSendEmail($user, $priority)) {
            $this->sendEmail($user, $type, $message, $context);
        }
    }

    /**
     * Send to multiple users at once.
     */
    public function notifyMany(iterable $users, string $type, string $message, array $context = [], string $priority = 'Informational'): void
    {
        foreach ($users as $user) {
            $this->notify($user, $type, $message, $context, $priority);
        }
    }

    /**
     * Create an in-app database notification.
     */
    public function createInApp(User $user, string $type, string $message, array $data = [], string $priority = 'Informational'): Notification
    {
        return Notification::create([
            'user_id'    => $user->id,
            'type'       => $type,
            'message'    => $message,
            'data'       => empty($data) ? null : $data,
            'priority'   => $priority,
            'created_at' => now(),
            'read_at'    => null,
        ]);
    }

    /**
     * Send an email to the user.
     */
    public function sendEmail(User $user, string $type, string $message, array $context = []): void
    {
        try {
            $appName = config('app.name', 'RDRIMS');
            $subject = $this->getEmailSubject($type, $context);

            Mail::html(
                $this->buildEmailHtml($type, $message, $context, $user),
                function ($mail) use ($user, $subject, $appName) {
                    $mail->to($user->email, $user->name)
                         ->subject($subject);
                }
            );
        } catch (\Exception $e) {
            // Log email failure but don't break the request
            logger()->error('Failed to send notification email', [
                'user_id' => $user->id,
                'type'    => $type,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * Build a simple HTML email body.
     */
    private function buildEmailHtml(string $type, string $message, array $context, User $user): string
    {
        $appName = config('app.name', 'RDRIMS');
        $link    = $context['link'] ?? url('/dashboard');
        $actionText = $context['action_text'] ?? 'View in Dashboard';

        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; background: #f4f7fc; padding: 20px;">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                    <tr>
                        <td style="background: #1a5276; padding: 20px; text-align: center;">
                            <h1 style="color: #fff; margin: 0; font-size: 20px;">{$appName}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 30px;">
                            <p style="color: #333; font-size: 14px; line-height: 1.6;">Dear <strong>{$user->name}</strong>,</p>
                            <p style="color: #333; font-size: 14px; line-height: 1.6;">{$message}</p>
                            <p style="text-align: center; margin: 25px 0;">
                                <a href="{$link}" style="display: inline-block; padding: 12px 24px; background: #1a5276; color: #fff; text-decoration: none; border-radius: 4px; font-size: 14px;">{$actionText}</a>
                            </p>
                            <p style="color: #999; font-size: 12px; margin-top: 20px;">This is an automated notification from {$appName}. You can manage your notification preferences in your profile settings.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function shouldSendEmail(User $user, string $priority): bool
    {
        // Check global setting
        $globalEnabled = \App\Models\Setting::where('key', 'enable_notifications')->value('value');

        if ($globalEnabled === 'false' || $globalEnabled === false) {
            return false;
        }

        if (empty($user->email)) {
            return false;
        }

        // Critical priority always sends email (unless user disabled all emails completely via email_notifications)
        if ($priority === 'Critical') {
            return (bool) $user->email_notifications;
        }

        // For other priorities, check user preferences
        if (!$user->email_notifications) {
            return false;
        }

        if ($priority === 'Important') {
            return (bool) $user->email_important;
        }

        if ($priority === 'Informational') {
            return (bool) $user->email_informational;
        }

        return false;
    }

    /**
     * Get a human-readable email subject for the notification type.
     */
    private function getEmailSubject(string $type, array $context): string
    {
        $subjects = [
            'user_registered'       => 'Welcome to RDRIMS – Account Created',
            'proposal_submitted'    => 'Proposal Submitted Successfully',
            'proposal_approved'     => 'Your Proposal Has Been Approved',
            'proposal_rejected'     => 'Proposal Status Update – Rejected',
            'reviewer_assigned'     => 'New Review Assignment',
            'review_deadline'       => 'Review Deadline Reminder',
            'call_published'        => 'New Call for Proposals',
            'funding_approved'      => 'Funding Approved – Project Started',
            'password_reset'        => 'Password Reset Request',
            'account_activated'     => 'Your Account Has Been Activated',
            'output_submitted'      => 'Output Submitted for Review',
            'output_approved'       => 'Output Approved',
            'finance_check_pending' => 'Finance Check Required',
            'ethics_request_pending'=> 'Ethics Clearance Requested',
        ];

        return $subjects[$type] ?? 'Notification from RDRIMS';
    }

    // ── Convenience methods for common events ─────────────────────────

    public function proposalSubmitted(User $submitter, string $proposalTitle, int $proposalId): void
    {
        $this->notify($submitter, 'proposal_submitted',
            "Your proposal \"{$proposalTitle}\" has been submitted successfully and is pending review.",
            ['link' => url("/proposals/{$proposalId}"), 'action_text' => 'View Proposal']
        );
    }

    public function proposalApproved(User $submitter, string $proposalTitle, int $projectId): void
    {
        $this->notify($submitter, 'proposal_approved',
            "Congratulations! Your proposal \"{$proposalTitle}\" has been approved. A project has been created.",
            ['link' => url("/projects/{$projectId}"), 'action_text' => 'View Project']
        );
    }

    public function proposalRejected(User $submitter, string $proposalTitle, string $reason): void
    {
        $this->notify($submitter, 'proposal_rejected',
            "Your proposal \"{$proposalTitle}\" has been rejected. Reason: {$reason}",
            ['link' => url("/proposals"), 'action_text' => 'View Proposals']
        );
    }

    public function reviewerAssigned(User $reviewer, string $proposalTitle, int $proposalId): void
    {
        $this->notify($reviewer, 'reviewer_assigned',
            "You have been assigned as a reviewer for \"{$proposalTitle}\". Please submit your review before the deadline.",
            ['link' => url("/reviewer/proposals/{$proposalId}"), 'action_text' => 'Start Review']
        );
    }

    public function callPublished(string $callTitle, int $callId): void
    {
        $researchers = User::whereHas('roles', fn($q) => $q->whereIn('name', ['researcher', 'research_admin']))
            ->where('is_active', true)
            ->get();

        $this->notifyMany($researchers, 'call_published',
            "A new call for proposals has been published: \"{$callTitle}\". Deadline: " . now()->addDays(30)->format('Y-m-d'),
            ['link' => url("/calls/{$callId}"), 'action_text' => 'View Call']
        );
    }

    public function outputSubmitted(User $student, string $outputTitle, int $outputId): void
    {
        $this->notify($student, 'output_submitted',
            "Your output \"{$outputTitle}\" has been submitted and is pending supervisor review.",
            ['link' => url("/outputs/{$outputId}"), 'action_text' => 'View Output']
        );
    }
}
