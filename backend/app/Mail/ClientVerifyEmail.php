<?php

namespace App\Mail;

use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;
use Symfony\Component\Mime\Email;

class ClientVerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Client $client,
        public ?int $appointmentId = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmación de email',
        );
    }

    public function content(): Content
    {
        $relativeUrl = URL::temporarySignedRoute(
            'clients.email.verify',
            now()->addDays(3),
            array_filter([
                'client' => (int) $this->client->id,
                'eh' => $this->client->emailVerificationHash(),
                'appointment' => $this->appointmentId,
            ], fn ($v) => $v !== null)
            ,
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
            view: 'emails.client-verify-email',
            with: [
                'client' => $this->client,
                'verifyUrl' => $url,
                'logoSrc' => $logoSrc,
            ],
        );
    }
}
