# SpaProgram (Backend)

## Emails (dev y producción)

Esta app envía emails en dos casos:

1) **Verificación del email del cliente** (cuando se carga/cambia el email)
2) **Confirmación de turno** (solo si el email del cliente está verificado)

### Dónde se configura el email “del spa” (remitente)

Eso se configura en el servidor, en el archivo `.env` (producción) o tu `.env` local:

- `MAIL_FROM_ADDRESS` → el email del spa (ej. `no-reply@tudominio.com`)
- `MAIL_FROM_NAME` → nombre visible (ej. `Spa DayOff`)

### Dónde se carga el email del cliente

En el panel:

- **Clientes** → campo `email`
- **Turnos** (cuando se crea un cliente nuevo desde el modal) → `client_email`

### Desarrollo (recomendado: Mailpit)

Para probar sin enviar correos reales (captura de emails):

1) Levantá Mailpit (Docker o binario) y abrí la bandeja web.
2) En `.env`:

```
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_ENCRYPTION=null
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="no-reply@local.test"
MAIL_FROM_NAME="SpaProgram"
```

3) Limpiá config cache:

`php artisan config:clear`

### Producción (Hostinger)

En Hostinger (hPanel) buscá los datos SMTP del email que vas a usar (normalmente en “Configurar cliente de correo” / “SMTP”).
Luego completá en el `.env` del servidor:

```
APP_URL=https://tudominio.com

MAIL_MAILER=smtp
MAIL_HOST=<smtp_hostinger>
MAIL_PORT=<587_o_465>
MAIL_ENCRYPTION=<tls_o_ssl>
MAIL_USERNAME=<tu_email_del_dominio>
MAIL_PASSWORD=<password_del_email>
MAIL_FROM_ADDRESS=<tu_email_del_dominio>
MAIL_FROM_NAME="Spa <Nombre>"
```

Después ejecutá:

`php artisan config:clear`

> Importante: en DNS configurá SPF/DKIM (y si podés DMARC) para buena entregabilidad.
