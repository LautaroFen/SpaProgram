@php
    /** @var \App\Models\User $user */
    /** @var string $verifyUrl */
    /** @var string|null $logoSrc */

    $displayName = trim((string) ($user->first_name.' '.$user->last_name));
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
                                Confirmá tu correo electrónico
                            </h2>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 24px 0 24px;font-family:Arial,Helvetica,sans-serif;color:#FFFFFF;">
                            <p style="margin:0;font-size:15px;line-height:24px;">
                                Hola <strong>{{ $displayName !== '' ? e($displayName) : '—' }}</strong>,
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:12px 24px 0 24px;font-family:Arial,Helvetica,sans-serif;color:#EAEAEA;">
                            <p style="margin:0;font-size:14px;line-height:22px;">
                                Recibiste este correo porque necesitamos confirmar tu dirección de email
                                para que puedas gestionar tus turnos y recibir notificaciones del sistema.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:26px 24px 18px 24px;">
                            <a href="{{ $verifyUrl }}"
                               target="_blank"
                               rel="noopener"
                               style="display:inline-block;
                                      background-color:#D4AF37;
                                      color:#000000;
                                      text-decoration:none;
                                      padding:14px 20px;
                                      border-radius:12px;
                                      font-family:Arial,Helvetica,sans-serif;
                                      font-size:15px;
                                      font-weight:bold;">
                                Verificar mi correo
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 24px 18px 24px;font-family:Arial,Helvetica,sans-serif;color:#BBBBBB;">
                            <p style="margin:0;font-size:12px;line-height:18px;">
                                Si el botón no funciona, copiá y pegá este enlace en tu navegador:
                            </p>

                            <p style="margin:6px 0 0 0;font-size:12px;word-break:break-all;overflow-wrap:anywhere;">
                                <a href="{{ $verifyUrl }}" target="_blank" rel="noopener" style="color:#D4AF37;text-decoration:underline;word-break:break-all;overflow-wrap:anywhere;">
                                    {{ $verifyUrl }}
                                </a>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 24px;">
                            <hr style="border:none;border-top:1px solid #333333;">
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 24px 28px 24px;font-family:Arial,Helvetica,sans-serif;color:#9A9A9A;text-align:center;">
                            <p style="margin:0;font-size:12px;line-height:18px;">
                                Si no solicitaste esta verificación, podés ignorar este mensaje.
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
