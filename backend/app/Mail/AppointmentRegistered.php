<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;

class AppointmentRegistered extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Appointment $appointment) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Turno registrado',
        );
    }

    public function content(): Content
    {
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
            view: 'emails.appointment-registered',
            with: [
                'appointment' => $this->appointment,
                'logoSrc' => $logoSrc,
            ],
        );
    }
}
