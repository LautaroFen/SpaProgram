param(
  [string]$OutputDir = "dist",
  [string]$LaravelDirName = "laravel_app",
  [string]$PublicHtmlDirName = "public_html",
  [switch]$CreateEnv,
  [switch]$PublicSubfolder
)

$ErrorActionPreference = "Stop"

$backendRoot = Resolve-Path (Join-Path $PSScriptRoot "..")
$timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
$distRoot = Join-Path $backendRoot (Join-Path $OutputDir ("hostinger-" + $timestamp))
$laravelOut = Join-Path $distRoot $LaravelDirName
$publicOut = Join-Path $distRoot $PublicHtmlDirName

New-Item -ItemType Directory -Force -Path $laravelOut | Out-Null
New-Item -ItemType Directory -Force -Path $publicOut | Out-Null

$vendorAutoload = Join-Path $backendRoot "vendor\autoload.php"
if (-not (Test-Path $vendorAutoload)) {
  throw "No se encontró vendor/autoload.php. Ejecutá 'composer install --no-dev --optimize-autoloader' en backend antes de empaquetar."
}

$viteManifest = Join-Path $backendRoot "public\build\manifest.json"
if (-not (Test-Path $viteManifest)) {
  throw "No se encontró public/build/manifest.json. Ejecutá 'npm install' y 'npm run build' en backend antes de empaquetar."
}

Write-Host "Copiando Laravel a: $laravelOut" -ForegroundColor Cyan

# Copia el proyecto completo (incluye vendor/ y public/build) excluyendo basura local.
$excludeDirs = @(
  "node_modules",
  ".git",
  "dist",
  "tests"
)

$excludeFiles = @(
  ".env"
)

$xd = @()
foreach ($d in $excludeDirs) { $xd += @("/XD", (Join-Path $backendRoot $d)) }

$xf = @()
foreach ($f in $excludeFiles) { $xf += @("/XF", (Join-Path $backendRoot $f)) }

# /E copia subdirectorios, incluso vacíos.
# /NFL /NDL reduce ruido.
robocopy $backendRoot $laravelOut /E /COPY:DAT /DCOPY:DAT @xd @xf /NFL /NDL /NJH /NJS | Out-Null

if ($CreateEnv) {
  Write-Host "Creando .env de producción en el paquete..." -ForegroundColor Cyan
  $appKey = "base64:" + [Convert]::ToBase64String((1..32 | ForEach-Object { Get-Random -Minimum 0 -Maximum 256 } | ForEach-Object { [byte]$_ }))

  $envPath = Join-Path $laravelOut ".env"
  $envContent = @"
APP_NAME=\"SpaProgram\"
APP_ENV=production
APP_KEY=$appKey
APP_DEBUG=false
APP_URL=https://tudominio.com

APP_LOCALE=es
APP_FALLBACK_LOCALE=en

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=spaprogram
DB_USERNAME=usuario
DB_PASSWORD=clave

# Defaults that work even without running migrations/SSH.
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync

FILESYSTEM_DISK=local

MAIL_MAILER=smtp
MAIL_HOST=smtp.tudominio.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=correo@tudominio.com
MAIL_PASSWORD=tu_password
MAIL_FROM_ADDRESS=\"correo@tudominio.com\"
MAIL_FROM_NAME=\"SpaProgram\"

VITE_APP_NAME=\"SpaProgram\"
"@

  Set-Content -Path $envPath -Value $envContent -Encoding UTF8
}

Write-Host "Copiando public/ a: $publicOut" -ForegroundColor Cyan

if ($PublicSubfolder) {
  $publicSub = Join-Path $publicOut "public"
  New-Item -ItemType Directory -Force -Path $publicSub | Out-Null
  robocopy (Join-Path $backendRoot "public") $publicSub /E /COPY:DAT /DCOPY:DAT /NFL /NDL /NJH /NJS | Out-Null

  # .htaccess en public_html que manda TODO a /public (evita loop)
  $rootHtaccessPath = Join-Path $publicOut ".htaccess"
  $rootHtaccessContent = @'
<IfModule mod_rewrite.c>
RewriteEngine On

RewriteCond %{REQUEST_URI} !^/public/
RewriteRule ^(.*)$ public/$1 [L,QSA]
</IfModule>
'@
  Set-Content -Path $rootHtaccessPath -Value $rootHtaccessContent -Encoding UTF8

  # Ajustar public/index.php para apuntar a ../.. (home) / LaravelDirName
  $indexPath = Join-Path $publicSub "index.php"
  $indexPhp = @"
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));






$laravelBase = __DIR__.'/../../$LaravelDirName';

// Determine if the application is in maintenance mode...
if (file_exists(
    $maintenance = $laravelBase.'/storage/framework/maintenance.php'
)) {
    require $maintenance;
}

// Register the Composer autoloader...
require $laravelBase.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $laravelBase.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
"@
  Set-Content -Path $indexPath -Value $indexPhp -Encoding UTF8
} else {
  robocopy (Join-Path $backendRoot "public") $publicOut /E /COPY:DAT /DCOPY:DAT /NFL /NDL /NJH /NJS | Out-Null

  # Reemplazar index.php para apuntar al directorio LaravelDirName (sibling de public_html)
  $indexPath = Join-Path $publicOut "index.php"
  $indexPhp = @"
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$laravelBase = __DIR__.'/../$LaravelDirName';

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $laravelBase.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $laravelBase.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $laravelBase.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
"@

  Set-Content -Path $indexPath -Value $indexPhp -Encoding UTF8

  # Asegurar que .htaccess exista (algunos zips lo pierden si el uploader filtra archivos ocultos)
  $htaccess = Join-Path $publicOut ".htaccess"
  if (-not (Test-Path $htaccess)) {
    $sourceHtaccess = Join-Path $backendRoot "public\.htaccess"
    if (Test-Path $sourceHtaccess) {
      Copy-Item -Force $sourceHtaccess $htaccess
    }
  }
}

# Zip
$zipPath = Join-Path $backendRoot (Join-Path $OutputDir ("hostinger-" + $timestamp + ".zip"))
Write-Host "Generando ZIP: $zipPath" -ForegroundColor Cyan
if (Test-Path $zipPath) { Remove-Item -Force $zipPath }

$compressed = $false
for ($i = 1; $i -le 3; $i++) {
  try {
    Compress-Archive -Path (Join-Path $distRoot "*") -DestinationPath $zipPath -Force
    $compressed = $true
    break
  } catch {
    Write-Host "Compress-Archive falló (intento $i/3). Reintentando..." -ForegroundColor Yellow
    Start-Sleep -Seconds 2
  }
}

if (-not $compressed) {
  $tar = Get-Command tar.exe -ErrorAction SilentlyContinue
  if (-not $tar) {
    throw "No se pudo generar el ZIP (archivos bloqueados) y no se encontró tar.exe para fallback. Cerrá procesos que estén usando 'dist/' (ej: antivirus/indexador) y reintentá."
  }

  Write-Host "Usando tar.exe como fallback para generar ZIP..." -ForegroundColor Yellow
  Push-Location $distRoot
  try {
    & tar.exe -a -c -f $zipPath $LaravelDirName $PublicHtmlDirName
  } finally {
    Pop-Location
  }
}

Write-Host "OK. Subí la carpeta '$LaravelDirName' fuera de public_html y el contenido de '$PublicHtmlDirName' dentro de public_html." -ForegroundColor Green
Write-Host "TIP: En Hostinger seteá PHP 8.2+ para el dominio." -ForegroundColor Yellow
