<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('mail:test {to : Email destino (ej: fen.lautaro@yahoo.com)}', function () {
    $to = (string) $this->argument('to');

    $this->line('Mail configuration (sin password):');
    $this->line('  MAIL_MAILER: '.(string) config('mail.default'));
    $this->line('  host: '.(string) config('mail.mailers.smtp.host'));
    $this->line('  port: '.(string) config('mail.mailers.smtp.port'));
    $this->line('  scheme: '.(string) config('mail.mailers.smtp.scheme'));
    $this->line('  username: '.(string) config('mail.mailers.smtp.username'));
    $this->line('  from: '.(string) config('mail.from.address').' ('.(string) config('mail.from.name').')');
    $this->newLine();

    // Force sync (no queue) for this diagnostic command.
    Config::set('mail.default', 'smtp');

    try {
        Mail::raw(
            "Hola Lautaro,\n\nEste es un correo de prueba enviado desde SpaProgram (Laravel).\n\nFecha: ".now()->toDateTimeString()."\nEntorno: ".app()->environment()."\n",
            function ($message) use ($to) {
                $message->to($to)->subject('SpaProgram - Test de correo');
            }
        );

        $this->info('✅ Enviado (si no llega, revisá SPAM y/o bloqueo del proveedor).');
    } catch (TransportExceptionInterface $e) {
        $this->error('❌ Falló el transporte SMTP: '.$e->getMessage());
        return self::FAILURE;
    } catch (Throwable $e) {
        $this->error('❌ Falló el envío: '.$e->getMessage());
        return self::FAILURE;
    }

    return self::SUCCESS;
})->purpose('Envía un correo de prueba y muestra el error si falla');
