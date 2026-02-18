@php
    /** @var \App\Models\Appointment $appointment */
        /** @var string|null $logoSrc */

        $clientName = trim((string) ($appointment->client?->full_name ?? ''));
        $serviceName = (string) ($appointment->service?->name ?? '—');
        $staffName = trim((string) (($appointment->user?->first_name ?? '').' '.($appointment->user?->last_name ?? '')));
        $staffName = $staffName !== '' ? $staffName : '—';

        $startAt = $appointment->start_at;
        $endAt = $appointment->end_at;
        $dateLabel = $startAt ? $startAt->format('d/m/Y') : '—';
        $timeLabel = $startAt ? $startAt->format('H:i') : '—';
        $endTimeLabel = $endAt ? $endAt->format('H:i') : '—';

        $priceCents = (int) ($appointment->price_cents ?? ($appointment->service?->price_cents ?? 0));
        $depositCents = (int) ($appointment->deposit_cents ?? 0);
        $price = number_format($priceCents / 100, 2, ',', '.');
        $deposit = number_format($depositCents / 100, 2, ',', '.');
@endphp

<div style="margin:0;padding:0;background-color:#000000;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#000000;padding:26px 12px;">
        <tr>
            <td align="center">

                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                    style="width:100%;max-width:520px;background:#000000;border:1px solid #D4AF37;border-radius:16px;overflow:hidden;">

                    <tr>
                        <td align="center" style="padding:32px 24px 10px 24px;">
                            <img
                                src="{{ $logoSrc ?: asset('images/LogoEmail.jpeg') }}"
                                alt="Logo"
                                width="140"
                                style="display:block;width:140px;max-width:100%;height:auto;"
                            />
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:10px 24px 0 24px;font-family:Arial,Helvetica,sans-serif;color:#FFFFFF;">
                            <h2 style="margin:0;font-size:20px;font-weight:700;">
                                Turno registrado
                            </h2>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 24px 0 24px;font-family:Arial,Helvetica,sans-serif;color:#FFFFFF;">
                            <p style="margin:0;font-size:15px;line-height:24px;">
                                Hola <strong>{{ $clientName !== '' ? e($clientName) : '—' }}</strong>,
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:12px 24px 0 24px;font-family:Arial,Helvetica,sans-serif;color:#EAEAEA;">
                            <p style="margin:0;font-size:14px;line-height:22px;">
                                Tu turno fue registrado correctamente. A continuación te dejamos los detalles:
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 24px 0 24px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation" style="border-collapse:separate;border-spacing:0;">
                                <tr>
                                    <td style="padding:10px 12px;border:1px solid #333333;border-radius:12px;">
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation">
                                            <tr>
                                                <td style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#BBBBBB;">Fecha</td>
                                                <td align="right" style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#FFFFFF;font-weight:700;">{{ $dateLabel }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#BBBBBB;">Horario</td>
                                                <td align="right" style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#FFFFFF;font-weight:700;">{{ $timeLabel }} – {{ $endTimeLabel }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#BBBBBB;">Servicio</td>
                                                <td align="right" style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#FFFFFF;font-weight:700;">{{ e($serviceName) }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#BBBBBB;">Profesional</td>
                                                <td align="right" style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#FFFFFF;font-weight:700;">{{ e($staffName) }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#BBBBBB;">Precio</td>
                                                <td align="right" style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#FFFFFF;font-weight:700;">$ {{ $price }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#BBBBBB;">Anticipo</td>
                                                <td align="right" style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#FFFFFF;font-weight:700;">$ {{ $deposit }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 24px 0 24px;">
                            <hr style="border:none;border-top:1px solid #333333;">
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 24px 28px 24px;font-family:Arial,Helvetica,sans-serif;color:#9A9A9A;text-align:center;">
                            <p style="margin:0;font-size:12px;line-height:18px;">
                                Si no reconocés este turno, respondé a este correo o contactanos.
                            </p>

                            <p style="margin:12px 0 0 0;font-size:11px;opacity:0.8;">
                                © {{ date('Y') }} Todos los derechos reservados.
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>
</div>
