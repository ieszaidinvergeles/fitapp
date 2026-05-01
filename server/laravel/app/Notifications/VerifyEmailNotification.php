<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

/**
 * Custom email verification notification for the GymApp API.
 *
 * SRP: Solely responsible for building and sending the verification email.
 * OCP: Extends Laravel's built-in VerifyEmail without modifying its core behaviour.
 *
 * NOTE: To use this notification, override sendEmailVerificationNotification()
 *       in the User model:
 *
 *       public function sendEmailVerificationNotification(): void
 *       {
 *           $this->notify(new VerifyEmailNotification());
 *       }
 */
class VerifyEmailNotification extends VerifyEmail
{
    /**
     * Builds the mail message sent to the user.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage())
            ->subject('Verify Your GymApp Email Address')
            ->greeting('Welcome to GymApp!')
            ->line('Please click the button below to verify your email address.')
            ->action('Verify Email', $verificationUrl)
            ->line('This verification link will expire in ' . Config::get('auth.verification.expire', 60) . ' minutes.')
            ->line('If you did not create an account, no further action is required.');
    }

    /**
     * Generates the signed verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl(mixed $notifiable): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id'   => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
