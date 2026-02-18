# SpaProgram

Sistema web para administrar un spa: **clientes**, **servicios**, **turnos**, **pagos**, **usuarios/roles** y **auditoría**.

Está construido con **Laravel + Blade + Tailwind + Vite**.

## Funcionalidades

- **Clientes**
	- Alta y edición.
	- Saldo/deuda por cliente (`balance_cents`) y filtro “Con deuda”.
	- Verificación de email del cliente (link firmado).
- **Servicios**
	- Alta de servicios con duración, precio y estado (activo/inactivo).
- **Turnos**
	- Alta de turno seleccionando cliente existente o creando uno nuevo.
	- Calcula fin estimado según duración del servicio.
	- Guarda precio del servicio en el turno.
	- **Al crear un turno incrementa la deuda del cliente** por: `precio - anticipo`.
	- Permite cargar **anticipo** (opcional) al registrar el turno.
	- En UI se reflejan estados como “Programado”, “Pago parcial”, “Atrasado”, “Pagado”, etc.
- **Pagos**
	- Registro de pago acreditado a un **cliente**.
	- Opción de asociarlo a un turno: valida que el turno pertenezca al cliente y evita pagar de más.
	- Al registrar un pago, **descuenta deuda** del cliente.
	- Si el pago cubre el saldo pendiente del turno, el turno puede cambiar a estado “paid”.
- **Usuarios y Roles (Admin)**
	- Gestión de usuarios del sistema y roles.
- **Audit Logs (Admin)**
	- Historial de acciones relevantes (altas/actualizaciones/estado, etc.).

## Requisitos

- **PHP 8.2+**
- **Composer**
- **Node.js (LTS) + npm**
- Base de datos:
	- El proyecto está pensado para correr con **MySQL** (en este repo ya está configurado así en `.env`).
	- Opcional: podés usar SQLite para pruebas rápidas, cambiando `DB_CONNECTION` en el `.env`.

## Instalación y uso (local)

Trabajá dentro de la carpeta `backend/`.

### Opción A (rápida): script de setup

Ejecuta instalación + `.env` + key + migraciones + npm + build:

```bash
cd backend
composer run setup
```

### Opción B (paso a paso)

```bash
cd backend
composer install
copy .env.example .env
php artisan key:generate
```

Configurá MySQL en el `.env` (ejemplo):

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=spaprogram
DB_USERNAME=root
DB_PASSWORD=
```

Creá la base de datos (una vez) y corré migraciones.

Opcional (SQLite):

- En `.env`: `DB_CONNECTION=sqlite`
- Asegurate de que exista el archivo:

```bash
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
```

Migraciones:

```bash
php artisan migrate
```

Frontend (Vite/Tailwind):

```bash
npm install
npm run build
```

### Levantar el proyecto en desarrollo

Opción recomendada (levanta **server + queue + logs + vite**):

```bash
composer run dev
```

Alternativa manual (en terminals separadas):

```bash
php artisan serve
```

```bash
npm run dev
```

Abrí: `http://127.0.0.1:8000`

## Usuarios demo (local/testing)

Para poder entrar al sistema rápido y probar roles, existe un seeder opcional que crea **1 usuario por rol**.

1) En `.env` (solo local):

```dotenv
SPA_SEED_DEMO_USERS=true
```

Opcionalmente definí emails/contraseñas (si la contraseña es muy corta, se autogenera):

```dotenv
SPA_DEMO_ADMIN_EMAIL=admin@local.test
SPA_DEMO_RECEPTION_EMAIL=recepcion@local.test
SPA_DEMO_PROFESSIONAL_EMAIL=profesional@local.test

# Recomendado: no definir passwords y dejar que se autogeneren.
# Si definís una password, debe ser larga (12+). Si es corta (ej. "1234"), se ignora
# y se genera una segura. Si el usuario ya existía y pusiste una password corta, se
# resetea a una autogenerada al seedear.
# SPA_DEMO_ADMIN_PASSWORD=una_clave_larga_de_12+
# SPA_DEMO_RECEPTION_PASSWORD=una_clave_larga_de_12+
# SPA_DEMO_PROFESSIONAL_PASSWORD=una_clave_larga_de_12+
```

2) Ejecutá seed (esto reinicia la DB si usás `migrate:fresh`):

```bash
php artisan migrate:fresh --seed
```

El comando muestra los usuarios creados y (si se autogeneró) las contraseñas.

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

Guía de deploy paso a paso: `DEPLOY_HOSTINGER.md`.

Ejemplo de variables de entorno para producción: `.env.hostinger.example`.

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
