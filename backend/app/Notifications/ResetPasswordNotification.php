<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;

class ResetPasswordNotification extends ResetPassword
{
    protected function resetUrl($notifiable): string
    {
        $frontendUrl = rtrim(config('app.frontend_url'), '/');

        return $frontendUrl.'/reset-password?'.http_build_query([
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
    }
}
