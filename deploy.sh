#!/bin/bash
set -e

echo "=== INICIANDO DESPLIEGUE: KERNELUMG COMMERCE ==="

# 1. Poner aplicación en modo mantenimiento temporal
php artisan down --render="errors.503" --secret="bypass-token-2026"

# 2. Descargar últimos cambios del repositorio
git pull origin main

# 3. Instalar dependencias PHP de producción sin paquetes de depuración
composer install --no-dev --optimize-autoloader --no-interaction

# 4. Migración de Base de Datos e Índices de Rendimiento
php artisan migrate --force

# 5. Limpieza y Reconstrucción de Cachés de Framework
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 6. Optimización de enlaces simbólicos de almacenamiento
php artisan storage:link || true

# 7. Reiniciar Daemons de Procesamiento Asíncrono de Correos y Tareas
sudo supervisorctl restart kernelumg-worker:* || true
sudo systemctl reload php8.2-fpm || true
sudo systemctl reload nginx || true

# 8. Reactivar el sistema en producción
php artisan up

echo "=== DESPLIEGUE CONCLUIDO CON ÉXITO ==="