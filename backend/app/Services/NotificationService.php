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
        $dbPriority = match (strtolower($priority)) {
            'informational', 'low' => 'low',
            'important', 'medium' => 'medium',
            'critical', 'high' => 'high',
            default => 'medium',
        };

        return Notification::create([
            'user_id'    => $user->id,
            'type'       => $type,
            'message'    => $message,
            'data'       => empty($data) ? null : $data,
            'priority'   => $dbPriority,
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
            'user_invited'          => 'You Have Been Invited to RDRIMS – Activate Your Account',
            'proposal_submitted'    => 'Proposal Submitted Successfully',
            'proposal_received'     => 'New Proposal Received for Review',
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

    /**
     * Send a professional invitation email to a newly provisioned user.
     *
     * Called exclusively by UserService::provision().
     * Never called during self-registration.
     * Reuses the Laravel Password Broker token for the activation link.
     *
     * @param User   $user          The provisioned user.
     * @param string $activationUrl The full activation URL including token & email.
     */
    public function sendInvitationEmail(User $user, string $activationUrl): void
    {
        $appName    = config('app.name', 'RDRIMS');
        $expireMin  = config('auth.passwords.users.expire', 60);
        $expireText = $expireMin >= 60
            ? ($expireMin / 60) . ' hour' . ($expireMin / 60 !== 1 ? 's' : '')
            : $expireMin . ' minutes';

        // Create in-app notification first (always, regardless of email settings)
        $this->createInApp(
            $user,
            'user_invited',
            "An account has been created for you on {$appName}. Click the activation link sent to your email to set your password and get started.",
            ['link' => $activationUrl, 'action_text' => 'Activate Account'],
            'Important'
        );

        // Send the invitation email directly — bypasses the shouldSendEmail() guard
        // because this is a transactional security email that must always be delivered.
        $subject = $this->getEmailSubject('user_invited', []);
        $html    = $this->buildInvitationHtml($user, $activationUrl, $expireText);
        try {
            Mail::html(
                $html,
                function ($mail) use ($user, $subject) {
                    $mail->to($user->email, $user->name)->subject($subject);
                }
            );
            logger()->info('Invitation email sent', ['user_id' => $user->id, 'email' => $user->email]);
        } catch (\Exception $e) {
            logger()->error('Failed to send invitation email', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'error'   => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Build a professional HTML invitation email body.
     * Distinct from buildEmailHtml() — purpose-built for account activation.
     */
    private function buildInvitationHtml(User $user, string $activationUrl, string $expireText): string
    {
        $appName = config('app.name', 'RDRIMS');

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Activate Your RDRIMS Account</title>
</head>
<body style="margin:0;padding:0;background:#f0f4f8;font-family:'Segoe UI',Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f4f8;padding:40px 20px;">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

          <!-- Header -->
          <tr>
            <td style="background:linear-gradient(135deg,#1a3a5c 0%,#1e5fa8 100%);padding:36px 40px;text-align:center;">
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td align="center">
                    <div style="display:inline-block;width:56px;height:56px;background:rgba(255,255,255,0.15);border-radius:14px;line-height:56px;text-align:center;font-size:28px;font-weight:900;color:#ffffff;margin-bottom:16px;">R</div>
                    <br />
                    <span style="color:#ffffff;font-size:22px;font-weight:700;letter-spacing:-0.5px;">{$appName}</span>
                    <br />
                    <span style="color:rgba(255,255,255,0.75);font-size:13px;font-weight:400;margin-top:4px;display:block;">Research &amp; Development Information Management System</span>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding:40px 40px 32px;">
              <p style="margin:0 0 8px;font-size:13px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:1px;">Account Invitation</p>
              <h1 style="margin:0 0 20px;font-size:26px;font-weight:700;color:#1a202c;line-height:1.3;">Welcome, {$user->name}! 🎉</h1>

              <p style="margin:0 0 16px;font-size:15px;color:#4b5563;line-height:1.7;">
                An account has been created for you on <strong>{$appName}</strong>. You're one step away from accessing the platform.
              </p>
              <p style="margin:0 0 28px;font-size:15px;color:#4b5563;line-height:1.7;">
                Click the button below to <strong>activate your account</strong>, create your password, and complete your profile.
              </p>

              <!-- CTA Button -->
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td align="center" style="padding:0 0 28px;">
                    <a href="{$activationUrl}"
                       style="display:inline-block;padding:16px 40px;background:linear-gradient(135deg,#1a3a5c 0%,#1e5fa8 100%);color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;border-radius:10px;letter-spacing:0.3px;box-shadow:0 4px 14px rgba(30,95,168,0.35);">
                      ✓ &nbsp; Activate My Account
                    </a>
                  </td>
                </tr>
              </table>

              <!-- Steps -->
              <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border-radius:12px;padding:20px;margin-bottom:24px;">
                <tr>
                  <td style="padding:0 0 12px;">
                    <p style="margin:0 0 12px;font-size:12px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:1px;">What happens next</p>
                  </td>
                </tr>
                <tr><td style="padding:4px 0;"><span style="color:#1e5fa8;font-weight:700;">① </span><span style="font-size:14px;color:#4b5563;">Click "Activate My Account"</span></td></tr>
                <tr><td style="padding:4px 0;"><span style="color:#1e5fa8;font-weight:700;">② </span><span style="font-size:14px;color:#4b5563;">Create your secure password</span></td></tr>
                <tr><td style="padding:4px 0;"><span style="color:#1e5fa8;font-weight:700;">③ </span><span style="font-size:14px;color:#4b5563;">Complete your researcher profile</span></td></tr>
                <tr><td style="padding:4px 0;"><span style="color:#1e5fa8;font-weight:700;">④ </span><span style="font-size:14px;color:#4b5563;">Access the full RDRIMS platform</span></td></tr>
              </table>

              <!-- Security Notice -->
              <div style="border-left:3px solid #f59e0b;padding:12px 16px;background:#fffbeb;border-radius:0 8px 8px 0;margin-bottom:24px;">
                <p style="margin:0;font-size:13px;color:#92400e;line-height:1.6;">
                  <strong>⏱ This activation link expires in {$expireText}.</strong><br />
                  If the link expires, contact your administrator to request a new invitation.
                </p>
              </div>

              <!-- Fallback URL -->
              <p style="margin:0 0 4px;font-size:12px;color:#9ca3af;">If the button does not work, copy and paste this link into your browser:</p>
              <p style="margin:0;font-size:11px;color:#6b7280;word-break:break-all;">{$activationUrl}</p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background:#f8fafc;padding:20px 40px;border-top:1px solid #e5e7eb;">
              <p style="margin:0;font-size:12px;color:#9ca3af;text-align:center;line-height:1.6;">
                This invitation was sent by <strong>{$appName}</strong>.<br />
                If you did not expect this email, you can safely ignore it — no account action will be taken until you click the activation link.<br />
                <strong>Never share this link with anyone.</strong>
              </p>
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

    public function proposalSubmitted(User $submitter, string $proposalTitle, int $proposalId): void
    {
        $this->notify($submitter, 'proposal_submitted',
            "Your proposal \"{$proposalTitle}\" has been submitted successfully and is pending review.",
            ['link' => url("/proposals/{$proposalId}"), 'action_text' => 'View Proposal']
        );
    }

    public function proposalReceived(User $admin, string $proposalTitle, int $proposalId, string $submitterName): void
    {
        $this->notify($admin, 'proposal_received',
            "A new proposal \"{$proposalTitle}\" has been submitted by {$submitterName} and is awaiting your review/action.",
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

    public function ethicsDecisionMade(User $submitter, string $proposalTitle, string $status, ?string $comment): void
    {
        $statusText = str_replace('_', ' ', $status);
        $this->notify($submitter, 'ethics_decision_made',
            "The ethics request for your proposal \"{$proposalTitle}\" has been updated to: {$statusText}." . ($comment ? " Remarks: {$comment}" : ""),
            ['link' => url("/proposals"), 'action_text' => 'View Proposals']
        );
    }
}
