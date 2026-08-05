#!/bin/sh

set -e

echo "Esperando que la base de datos esté disponible..."
sleep 5

echo "Limpiando cache..."
php artisan optimize:clear

echo "Ejecutando migraciones..."
php artisan migrate --force

echo "Ejecutando seeders..."
php artisan db:seed --force

echo "Iniciando Apache..."
exec apache2-foreground
