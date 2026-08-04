# Deploy en hosting compartido (subida manual)

Esta guia deja el proyecto listo para hosting compartido gratuito cuando la subida de archivos se hace manual.

## 1) Preparar build de produccion en local

Ejecuta estos comandos en tu maquina local antes de subir:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize
```

Verifica que existan estas carpetas antes de subir:

- `vendor/`
- `public/build/`
- `storage/framework/`
- `bootstrap/cache/`

## 2) Configurar variables de entorno

1. Crea `.env` en el servidor usando como base `.env.shared.example`.
2. Ajusta al menos estas variables:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://tu-dominio.com`
- `DB_CONNECTION=mysql`
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`

Genera una llave unica (si tienes terminal SSH):

```bash
php artisan key:generate --force
```

Si no tienes terminal SSH, copia manualmente una `APP_KEY` valida desde tu entorno local de produccion.

## 3) Estructura recomendada en hosting compartido

### Opcion A (recomendada): dominio apuntando a `/public`

- Sube el proyecto completo fuera de `public_html` (por ejemplo: `/home/usuario/inscripcionesu`).
- Configura el dominio para que el Document Root apunte a `/home/usuario/inscripcionesu/public`.

### Opcion B: si NO puedes cambiar Document Root

Usa esta estructura:

- Codigo Laravel (app, bootstrap, config, database, resources, routes, storage, vendor, etc.) en:
    - `/home/usuario/inscripcionesu_core`
- Contenido de la carpeta `public/` dentro de:
    - `/home/usuario/public_html`

Luego edita `public_html/index.php` y reemplaza rutas para apuntar al core:

```php
require __DIR__.'/../inscripcionesu_core/vendor/autoload.php';
$app = require_once __DIR__.'/../inscripcionesu_core/bootstrap/app.php';
```

## 4) Permisos necesarios

Asegura permisos de escritura para:

- `storage/`
- `bootstrap/cache/`

Valores comunes:

- Carpetas: `775`
- Archivos: `664`

## 5) Base de datos

Si tienes terminal SSH:

```bash
php artisan migrate --force
php artisan db:seed --force
```

Si NO tienes terminal SSH:

1. Exporta la base en local a SQL.
2. Importa el SQL con phpMyAdmin en el hosting.

## 6) Verificaciones post-deploy

- Login responde en `/login`.
- Formulario publico responde en `/`.
- Reporte interno carga y exporta Excel/PDF.
- Se puede escribir en `storage/logs/laravel.log`.

## 7) Endurecimiento minimo de seguridad

- `APP_DEBUG=false`
- No exponer `.env`
- No subir carpetas de desarrollo innecesarias (`tests/`, `.git/`, `node_modules/`)

## 8) Checklist rapido para subida manual

- `vendor/` incluido
- `public/build/` incluido
- `.env` de produccion configurado
- permisos en `storage/` y `bootstrap/cache/`
- migraciones aplicadas o SQL importado

## 9) InfinityFree: error 403 Forbidden (caso comun)

En InfinityFree, este 403 casi siempre ocurre porque el dominio esta leyendo `htdocs` sin un `index.php` de Laravel correcto o porque el proyecto se subio con estructura incorrecta.

### Estructura correcta en InfinityFree

InfinityFree no permite cambiar Document Root como en otros hostings, por eso usa esta estructura:

- `htdocs/`:
    - contenido de la carpeta `public/` de Laravel (`index.php`, `.htaccess`, `build/`, etc.)
- fuera de `htdocs/` (por ejemplo `inscripcionesu_core/`):
    - `app/`, `bootstrap/`, `config/`, `database/`, `resources/`, `routes/`, `storage/`, `vendor/`, `.env`, etc.

### Ajuste obligatorio de `htdocs/index.php`

En `htdocs/index.php`, cambia las rutas para apuntar al core:

```php
require __DIR__.'/../inscripcionesu_core/vendor/autoload.php';
$app = require_once __DIR__.'/../inscripcionesu_core/bootstrap/app.php';
```

### Verificaciones rapidas para quitar el 403

1. No dejes el proyecto como `htdocs/public/...`; debe quedar directo en `htdocs/...`.
2. Verifica que exista `htdocs/.htaccess` (archivo oculto).
3. Elimina archivos por defecto de InfinityFree que puedan interferir (por ejemplo `index2.html`).
4. Confirma que `vendor/` exista en `inscripcionesu_core/`.
5. En `.env` usa:
    - `APP_ENV=production`
    - `APP_DEBUG=false`
    - `APP_URL=https://tu-dominio`

Si luego de esto aparece error 500, ya no es problema de 403/estructura: normalmente sera ruta en `index.php`, datos de `.env` o permisos.
