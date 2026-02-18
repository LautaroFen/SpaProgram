# Deploy en Hostinger (Laravel)

Esta guía deja el proyecto listo para correr en **Hostinger** (hosting compartido) con **Laravel 12 + Vite**.

## Si Hostinger dice “framework no compatible”

Ese mensaje casi siempre aparece por una de estas causas:

- Estás intentando subirlo usando un instalador/validador de “frameworks” de Hostinger (no sirve para Laravel subido como ZIP).
- El servidor está usando una versión de PHP menor a la requerida.
- Falta alguna extensión de PHP.
- Subiste el ZIP sin `vendor/` o sin assets compilados (`public/build`).

Para este proyecto en particular:

- En [composer.json](composer.json) se exige `php: ^8.2` (Laravel 12). En Hostinger tenés que seleccionar **PHP 8.2+**.

## Requisitos en Hostinger

- PHP **8.2+**
- Extensiones típicas de Laravel habilitadas (si alguna falta, pueden aparecer errores): `openssl`, `mbstring`, `pdo_mysql`, `fileinfo`, `tokenizer`, `ctype`, `json`.
- `mod_rewrite` (Apache) habilitado.

> Nota: para usar la exportación ZIP de auditoría, el servidor debe tener la extensión `zip` (`ZipArchive`).

## Opción recomendada: apuntar el Document Root a `public/`

La forma correcta en producción es que el dominio apunte al directorio:

- `backend/public`

En Hostinger suele ser:
- `public_html` **no** debería contener todo Laravel.
- Debe contener **solo** lo de `public/`.

### Pasos

1) Subí el proyecto al servidor.

2) Configurá el dominio/subdominio para que su **Document Root** apunte a la carpeta `public` del proyecto.

3) Creá tu `.env` de producción usando el ejemplo:

- Copiá `.env.hostinger.example` a `.env` y completá valores.

4) En terminal/SSH (si está disponible) ejecutá:

- `php artisan key:generate`
- `php artisan migrate --force`

5) Asegurá permisos de escritura:

- `storage/`
- `bootstrap/cache/`

6) Si usás disco `public` (archivos visibles desde web), creá el symlink:

- `php artisan storage:link`

7) Opcional (mejora performance):

- `php artisan config:cache`
- `php artisan route:cache`

## Si NO podés cambiar el Document Root

Esto es más frágil. La alternativa típica es:

- Dejar el proyecto **fuera** de `public_html`.
- Copiar el contenido de `backend/public` dentro de `public_html`.
- Ajustar los paths en `public_html/index.php` para que apunten al directorio real del proyecto.

Ejemplo conceptual (paths ilustrativos):

```php
require __DIR__.'/../laravel/vendor/autoload.php';
$app = require_once __DIR__.'/../laravel/bootstrap/app.php';
```

Si querés esta variante, decime cómo queda tu estructura en Hostinger (rutas reales) y te digo exactamente qué líneas cambiar.

### Empaquetado automático (recomendado)

En este repo tenés un script que arma un ZIP con la estructura típica de Hostinger:

- `laravel_app/` (todo el proyecto Laravel, incluyendo `vendor/`)
- `public_html/` (solo lo de `public/`, con un `index.php` ya ajustado para apuntar a `laravel_app/`)

Pasos (en tu PC):

1) En `backend/` ejecutá:

- `composer install --no-dev --optimize-autoloader`
- `npm install`
- `npm run build`

2) Generá el ZIP:

- `powershell -ExecutionPolicy Bypass -File tools/package-hostinger.ps1`

Esto crea `backend/dist/hostinger-YYYYMMDD-HHMMSS.zip`.

3) En Hostinger (File Manager):

- Subí y descomprimí el ZIP en tu home.
- Mové `public_html/*` dentro de `public_html` del hosting.
- Dejá `laravel_app/` al lado (en el mismo nivel que `public_html`).

## Variables `.env` importantes (producción)

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://tudominio.com`
- DB (MySQL): `DB_CONNECTION=mysql`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- Mail (SMTP de Hostinger o el proveedor que uses): `MAIL_*`
- Recomendado en hosting compartido sin worker:
  - `QUEUE_CONNECTION=sync`

## Frontend (Vite)

Para Hostinger, lo más simple es **subir ya compilado** el contenido de `public/build`.
En este repo ya se genera con:

- `npm run build`

## Checklist rápido (para que prenda)

- PHP del dominio en Hostinger: **8.2+**
- `.env` creado (podés partir de `.env.hostinger.example`) con `APP_KEY` seteada
- Base de datos creada y credenciales en `.env`
- Migraciones corridas: `php artisan migrate --force`
- Permisos de escritura: `storage/` y `bootstrap/cache/`

