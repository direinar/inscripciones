# Laravel Boost + GitHub Copilot Setup

Esta guia deja una configuracion estable de IA para este proyecto.

## 1) Estado actual

- `laravel/boost` ya esta agregado en `require-dev`.
- Se registro `Laravel\\Boost\\BoostServiceProvider` de forma condicional en `bootstrap/providers.php` para evitar fallos en produccion con `--no-dev`.

## 2) Activar Boost en local

Ejecuta en PowerShell (como administrador si hay bloqueos por antivirus/indexador):

```powershell
composer install
php artisan boost:install
```

Si aparece un error de archivos bloqueados en `vendor/composer/*`, intenta:

```powershell
composer clear-cache
Remove-Item -Recurse -Force vendor\\composer\\* -ErrorAction SilentlyContinue
composer install
php artisan package:discover --ansi
php artisan boost:install
```

## 3) Flujo recomendado con Copilot

- Usa prompts concretos con contexto de archivo y regla de negocio.
- Pide cambios pequenos y verificables (ejemplo: una ruta, una vista, una migracion).
- Para cambios financieros, pedir siempre validacion de netos y devoluciones.

## 4) Validaciones utiles

```powershell
php -l app\\Http\\Controllers\\EnrollmentController.php
php artisan optimize:clear
php artisan view:cache
vendor\\bin\\pest --filter=EnrollmentTest
```

## 5) En produccion

- Mantener `APP_ENV=production` y `APP_DEBUG=false`.
- Instalar dependencias con `composer install --no-dev`.
- El provider condicional evita romper arranque si Boost no esta instalado en produccion.
