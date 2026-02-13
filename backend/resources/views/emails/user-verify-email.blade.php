@php
    /** @var \App\Models\User $user */
    /** @var string $verifyUrl */
    /** @var string|null $logoSrc */

    $displayName = trim((string) ($user->first_name.' '.$user->last_name));
@endphp

<div style="margin:0;padding:0;background:#000000;font-family:Arial,Helvetica,sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">

  <!-- outer full width wrapper -->
  <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation" style="background:#000000;">
    <tr>
      <td align="center" style="padding:28px 12px;">

        <!-- Fluid card: grows on desktop, never overflows on mobile -->
        <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation"
               align="center"
               style="width:100%;max-width:520px;background:#000000;border:1px solid #D4AF37;border-radius:16px;overflow:hidden;">

          <tr>
            <td style="padding:32px 24px;box-sizing:border-box;color:#FFFFFF;">

              <!-- Logo -->
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

              <!-- Título -->
              <div style="text-align:center;font-size:20px;font-weight:700;line-height:1.3;margin-bottom:14px;word-break:break-word;overflow-wrap:break-word;white-space:normal;">
                Confirmá tu correo
              </div>

              <!-- Saludo -->
              <div style="font-size:15px;color:#FFFFFF;margin-bottom:12px;line-height:1.55;word-break:break-word;overflow-wrap:break-word;white-space:normal;">
                Hola <strong>{{ $displayName !== '' ? e($displayName) : '—' }}</strong>,
              </div>

              <!-- Mensaje -->
              <div style="font-size:14px;color:#D6D6D6;margin-bottom:22px;line-height:1.65;word-break:break-word;overflow-wrap:break-word;white-space:normal;">
                Necesitamos verificar tu email para que puedas gestionar turnos y recibir recordatorios.
              </div>

              <!-- BOTÓN (full width dentro del card) -->
              <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation" style="margin-bottom:18px;">
                <tr>
                  <td align="center">
                    <a href="{{ $verifyUrl }}" target="_blank" rel="noopener noreferrer"
                        style="display:inline-block;width:100%;max-width:360px;text-align:center;padding:14px 18px;background:#D4AF37;color:#000000;text-decoration:none;border-radius:12px;font-weight:700;font-size:15px;line-height:1.2;">
                      Verificar correo
                    </a>
                  </td>
                </tr>
              </table>

              <!-- LINK DE RESPALDO: no mostramos la URL completa.
                   Mostramos texto corto que apunta a la URL real. -->
              <div style="font-size:12px;color:#9E9E9E;line-height:1.45;word-break:break-word;overflow-wrap:break-word;white-space:normal;">
                Si el botón no funciona, tocá este enlace:
                <div style="margin-top:8px;text-align:center;">
                  <a href="{{ $verifyUrl }}" target="_blank" rel="noopener noreferrer"
                     style="color:#D4AF37;font-size:13px;text-decoration:underline;word-break:break-all;overflow-wrap:anywhere;">
                    Copiar enlace de verificación
                  </a>
                </div>
              </div>

              <!-- Small footer -->
              <div style="font-size:10px;color:#777777;text-align:center;margin-top:20px;line-height:1.4;">
                Si no solicitaste esto, ignorá el mensaje.<br>© {{ date('Y') }}
              </div>

            </td>
          </tr>
        </table>

      </td>
    </tr>
  </table>
</div>
