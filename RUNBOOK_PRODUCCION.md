# Runbook de Despliegue a Produccion (app-services-ula)

Este runbook resume el procedimiento operativo para dos escenarios:

- Primera salida (instalacion limpia)
- Actualizacion de una produccion existente

## 1) Variables de entorno minimas

Validar en `.env`:

- `APP_ENV=production`
- `APP_DEBUG=false`
- Conexion real de base de datos (`DB_*`)
- `APP_KEY` configurada
- SMTP real (`MAIL_*`) si se requieren correos
- Cola en produccion (`QUEUE_CONNECTION`) segun infraestructura

## 2) Build de aplicacion

Desde la raiz del proyecto:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run prod
```

## 3) Escenario A: Primera salida (BD limpia)

1. Ejecutar migraciones:

```bash
php artisan migrate --force
```

2. Seeders minimos (solo data base de arranque):

```bash
php artisan db:seed --class=UserRoleSeeder --force
php artisan db:seed --class=CreatePepitaAdmin --force
```

3. Cambiar inmediatamente la clave del admin tecnico creado por seeder.

## 4) Escenario B: Actualizacion de produccion existente

1. Ejecutar solo migraciones:

```bash
php artisan migrate --force
```

2. No ejecutar seeders, salvo que haya una necesidad controlada y aprobada (por ejemplo, faltan roles base).

## 5) Optimizacion Laravel (post deploy)

```bash
php artisan config:cache
php artisan view:cache
```

Importante para este proyecto:

- No ejecutar `php artisan route:cache` porque hay rutas con closures en `routes/web.php`.

## 6) Verificacion rapida post despliegue

- Login de usuario administrador valido
- Creacion de ticket valida
- Envio de correo saliente (si aplica)
- Cola funcionando (si `QUEUE_CONNECTION` no es `sync`)
- Logs sin errores criticos en `storage/logs/laravel.log`

## 7) Regla de oro para seeders en produccion

- Migraciones: si, en cada release.
- Seeders: no por defecto; solo en inicializacion o correccion puntual controlada.

## 8) Comandos de referencia (bloque unico)

### Primera salida

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run prod
php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --class=UserRoleSeeder --force
php artisan db:seed --class=CreatePepitaAdmin --force
php artisan config:cache
php artisan view:cache
```

### Actualizacion

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run prod
php artisan migrate --force
php artisan config:cache
php artisan view:cache
```
