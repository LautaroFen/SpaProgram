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

<div style="margin:0;padding:0;background:#000000;font-family:Arial,Helvetica,sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation" style="background:#000000;">
        <tr>
            <td align="center" style="padding:28px 12px;">

                <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation"
                             align="center"
                             style="width:100%;max-width:520px;background:#000000;border:1px solid #D4AF37;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td style="padding:32px 24px;box-sizing:border-box;color:#FFFFFF;">

                            <table width="100%" role="presentation" cellpadding="0" cellspacing="0" border="0" style="table-layout:fixed;">
                                <tr>
                                    <td align="center" style="padding-bottom:18px;">
                                        <img src="{{ $logoSrc ?: asset('images/LogoNegro.jpeg') }}"
                                                 alt="Logo"
                                                 width="140"
                                                 style="display:block;border:0;outline:none;text-decoration:none;width:140px;height:auto;max-width:100%;" />
                                    </td>
                                </tr>
                            </table>

                            <div style="text-align:center;font-size:20px;font-weight:700;line-height:1.3;margin-bottom:10px;word-break:break-word;overflow-wrap:break-word;white-space:normal;">
                                Turno registrado
                            </div>

                            <div style="text-align:center;font-size:13px;color:#D6D6D6;line-height:1.5;margin-bottom:22px;">
                                {{ $clientName !== '' ? e($clientName) : 'Tu turno fue registrado correctamente.' }}
                            </div>

                            <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation" style="border-collapse:separate;border-spacing:0;">
                                <tr>
                                    <td style="padding:10px 12px;border:1px solid #2B2B2B;border-radius:12px;">
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation">
                                            <tr>
                                                <td style="padding:6px 0;font-size:13px;color:#9E9E9E;">Fecha</td>
                                                <td align="right" style="padding:6px 0;font-size:14px;color:#FFFFFF;font-weight:700;">{{ $dateLabel }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:6px 0;font-size:13px;color:#9E9E9E;">Horario</td>
                                                <td align="right" style="padding:6px 0;font-size:14px;color:#FFFFFF;font-weight:700;">{{ $timeLabel }} – {{ $endTimeLabel }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:6px 0;font-size:13px;color:#9E9E9E;">Servicio</td>
                                                <td align="right" style="padding:6px 0;font-size:14px;color:#FFFFFF;font-weight:700;">{{ e($serviceName) }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:6px 0;font-size:13px;color:#9E9E9E;">Profesional</td>
                                                <td align="right" style="padding:6px 0;font-size:14px;color:#FFFFFF;font-weight:700;">{{ e($staffName) }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:6px 0;font-size:13px;color:#9E9E9E;">Precio</td>
                                                <td align="right" style="padding:6px 0;font-size:14px;color:#FFFFFF;font-weight:700;">$ {{ $price }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:6px 0;font-size:13px;color:#9E9E9E;">Anticipo</td>
                                                <td align="right" style="padding:6px 0;font-size:14px;color:#FFFFFF;font-weight:700;">$ {{ $deposit }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <div style="font-size:11px;color:#777777;text-align:center;margin-top:22px;line-height:1.4;">
                                Si no reconocés este turno, respondé a este correo o contactanos.<br />© {{ date('Y') }}
                            </div>

                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>
</div>
