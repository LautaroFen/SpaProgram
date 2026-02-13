<?php

namespace App\Http\Controllers;

use App\Mail\AppointmentRegistered;
use App\Models\Appointment;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ClientEmailVerificationController extends Controller
{
    public function verify(Request $request, Client $client)
    {
        // Signature validation is handled by the `signed` middleware.
        $expectedHash = $client->emailVerificationHash();
        $providedHash = (string) $request->query('eh', '');

        if ($client->email === null || ! hash_equals($expectedHash, $providedHash)) {
            return response()->view('pages.email-verified', [
                'ok' => false,
                'message' => 'El link no es válido o el email del cliente cambió.',
            ], 400);
        }

        if (! $client->hasVerifiedEmail()) {
            $client->markEmailAsVerified();
        }

        $appointmentId = $request->query('appointment');
        if (is_string($appointmentId) && ctype_digit($appointmentId)) {
            $appointment = Appointment::query()
                ->with(['client', 'service', 'user'])
                ->where('id', (int) $appointmentId)
                ->where('client_id', (int) $client->id)
                ->first();

            if ($appointment && $client->email) {
                try {
                    Mail::to($client->email)->send(new AppointmentRegistered($appointment));
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        return view('pages.email-verified', [
            'ok' => true,
            'message' => 'Email verificado correctamente.',
        ]);
    }
}
