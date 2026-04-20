<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserEmailVerificationController extends Controller
{
    public function verify(Request $request, User $user)
    {
        // Signature validation is handled by the `signed` middleware.
        $expectedHash = $user->emailVerificationHash();
        $providedHash = (string) $request->query('eh', '');

        if ($user->email === null || ! hash_equals($expectedHash, $providedHash)) {
            return response()->view('pages.email-verified', [
                'ok' => false,
                'message' => 'El link no es válido o el email del usuario cambió.',
            ], 400);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return view('pages.email-verified', [
            'ok' => true,
            'message' => 'Email verificado correctamente.',
        ]);
    }
}
