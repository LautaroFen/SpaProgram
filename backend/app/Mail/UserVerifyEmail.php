<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;
use Symfony\Component\Mime\Email;

class UserVerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmación de email',
        );
    }

    public function content(): Content
    {
        $relativeUrl = URL::temporarySignedRoute(
            'users.email.verify',
            now()->addDays(3),
            [
                'user' => (int) $this->user->id,
                'eh' => $this->user->emailVerificationHash(),
            ],
            absolute: false,
        );

        $url = url($relativeUrl);

        $logoSrc = asset('images/LogoEmail.jpeg');
        $logoPath = public_path('images/LogoEmail.jpeg');
        if (is_string($logoPath) && is_file($logoPath)) {
            $cid = 'logoemail';
            $logoSrc = 'cid:'.$cid;

            $this->withSymfonyMessage(function (Email $message) use ($logoPath, $cid) {
                $message->embedFromPath($logoPath, $cid, 'image/jpeg');
            });
        }

        return new Content(
            view: 'emails.user-verify-email',
            with: [
                'user' => $this->user,
                'verifyUrl' => $url,
                'logoSrc' => $logoSrc,
            ],
        );
    }
}
