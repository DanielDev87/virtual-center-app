# Manual de Produccion Paso a Paso - app-services-ula

Este manual esta pensado para un despliegue real de Laravel en Linux con Nginx, PHP-FPM y MySQL/MariaDB.
Incluye preparacion, despliegue inicial, verificacion, operacion y rollback.

## 0. Objetivo y alcance

Objetivo:
- Lanzar la aplicacion en produccion con seguridad, trazabilidad y posibilidad de rollback.

Alcance:
- Ubuntu 22.04 o 24.04
- Nginx + PHP 8.2 + MySQL 8 o MariaDB 10.6+
- Build de frontend con Node 18+
- Operacion con logs, backups, workers y scheduler (si aplica)

## 1. Checklist previo obligatorio

Antes de tocar produccion, confirma:

1. Dominio y DNS apuntando al servidor.
2. Certificado TLS disponible (Lets Encrypt o corporativo).
3. Acceso SSH con usuario de despliegue no-root.
4. Variables de entorno de produccion definidas.
5. Plan de backup y plan de rollback aprobados.
6. Ventana de mantenimiento comunicada.

## 2. Requisitos del servidor

Minimos recomendados:
- 2 vCPU (4 recomendado)
- 4 GB RAM (8 recomendado)
- 40 GB SSD

Paquetes base:

```bash
sudo apt update
sudo apt install -y nginx mysql-server unzip git curl supervisor
sudo apt install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-bcmath php8.2-intl php8.2-gd php8.2-fileinfo
```

Composer:

```bash
cd /tmp
curl -sS https://getcomposer.org/installer -o composer-setup.php
php composer-setup.php
sudo mv composer.phar /usr/local/bin/composer
```

Node.js 18:

```bash
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs
```

Verifica versiones:

```bash
php -v
composer -V
node -v
npm -v
nginx -v
```

## 3. Estructura recomendada de despliegue

Usa estrategia por releases para rollback rapido:

- /var/www/app-services-ula/releases/<timestamp>
- /var/www/app-services-ula/shared/.env
- /var/www/app-services-ula/shared/storage
- /var/www/app-services-ula/current -> symlink al release activo

Crear estructura inicial:

```bash
sudo adduser --disabled-password --gecos "" deploy
sudo usermod -aG www-data deploy
sudo mkdir -p /var/www/app-services-ula/releases
sudo mkdir -p /var/www/app-services-ula/shared/storage
sudo chown -R deploy:www-data /var/www/app-services-ula
```

## 4. Configuracion de base de datos

Crear base y usuario:

```sql
CREATE DATABASE app_services_ula CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'app_user'@'127.0.0.1' IDENTIFIED BY 'CAMBIAR_CLAVE_SEGURA';
GRANT ALL PRIVILEGES ON app_services_ula.* TO 'app_user'@'127.0.0.1';
FLUSH PRIVILEGES;
```

## 5. Variables de entorno de produccion

Crear / actualizar shared .env:

```bash
cp env.example /var/www/app-services-ula/shared/.env
nano /var/www/app-services-ula/shared/.env
```

Valores base recomendados:

```env
APP_NAME="Virtual Center"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

LOG_CHANNEL=stack
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=app_services_ula
DB_USERNAME=app_user
DB_PASSWORD=CAMBIAR_CLAVE_SEGURA

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

SESSION_SECURE_COOKIE=true
```

Notas:
- En produccion no usar APP_DEBUG=true.
- SESSION_SECURE_COOKIE=true exige HTTPS.
- Si vas a usar colas reales, migrar QUEUE_CONNECTION a database o redis.

## 6. Primer despliegue (paso a paso)

1) Crear release:

```bash
TS=$(date +%Y%m%d%H%M%S)
mkdir -p /var/www/app-services-ula/releases/$TS
cd /var/www/app-services-ula/releases/$TS
git clone <URL_DEL_REPO> .
```

2) Instalar dependencias backend y frontend:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run prod
```

3) Conectar archivos compartidos:

```bash
ln -s /var/www/app-services-ula/shared/.env .env
rm -rf storage
ln -s /var/www/app-services-ula/shared/storage storage
```

4) Laravel bootstrap:

```bash
php artisan key:generate --force
php artisan migrate --force
php artisan storage:link
```

5) Cache de framework:

```bash
php artisan config:cache
php artisan view:cache
```

Importante para este proyecto:
- No ejecutar php artisan route:cache.
- Este proyecto tiene rutas con closures en routes/web.php y route:cache fallaria.

6) Permisos:

```bash
sudo chown -R deploy:www-data /var/www/app-services-ula
sudo find /var/www/app-services-ula -type f -exec chmod 664 {} \;
sudo find /var/www/app-services-ula -type d -exec chmod 775 {} \;
sudo chmod -R 775 /var/www/app-services-ula/shared/storage
sudo chmod -R 775 /var/www/app-services-ula/releases/$TS/bootstrap/cache
```

7) Publicar release activo:

```bash
ln -sfn /var/www/app-services-ula/releases/$TS /var/www/app-services-ula/current
```

## 7. Nginx en produccion

Archivo sugerido /etc/nginx/sites-available/app-services-ula:

```nginx
server {
    listen 80;
    server_name tu-dominio.com;
    root /var/www/app-services-ula/current/public;

    index index.php index.html;
    client_max_body_size 25M;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header Referrer-Policy "strict-origin-when-cross-origin";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_read_timeout 300;
    }

    location ~ /\.ht {
        deny all;
    }

    access_log /var/log/nginx/app-services-ula.access.log;
    error_log /var/log/nginx/app-services-ula.error.log;
}
```

Activar sitio:

```bash
sudo ln -s /etc/nginx/sites-available/app-services-ula /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## 8. HTTPS obligatorio

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d tu-dominio.com
sudo certbot renew --dry-run
```

## 9. Datos iniciales segun estrategia

### Opcion A: Produccion con datos vacios (carga masiva posterior)

```bash
php artisan migrate:fresh --force
```

No ejecutar seeders.

### Opcion B: Solo roles + admin tecnico de arranque

```bash
php artisan migrate:fresh --force
php artisan db:seed --class=UserRoleSeeder --force
php artisan db:seed --class=CreatePepitaAdmin --force
```

## 10. Worker de colas (si QUEUE_CONNECTION != sync)

Crear /etc/supervisor/conf.d/laravel-worker.conf:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/app-services-ula/current/artisan queue:work --sleep=3 --tries=3 --timeout=120
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=deploy
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/app-services-ula/current/storage/logs/worker.log
```

Aplicar:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

## 11. Scheduler (si se usa)

Agregar cron del usuario deploy:

```cron
* * * * * cd /var/www/app-services-ula/current && php artisan schedule:run >> /dev/null 2>&1
```

## 12. Hardening minimo recomendado

1. Bloquear acceso SSH por password (usar llaves).
2. Activar firewall:

```bash
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

3. Mantener APP_DEBUG=false.
4. No exponer .env ni logs por web.
5. Rotacion de logs activa en SO.
6. Backups cifrados fuera del servidor.

## 13. Verificacion post despliegue (go-live checklist)

Ejecutar y documentar:

1. Abre home y login en HTTPS.
2. Crear ticket desde Portal Publico.
3. Consultar ticket en seguimiento.
4. Validar carga de adjuntos.
5. Validar envio de correo (si SMTP productivo).
6. Revisar logs por errores 500.
7. Revisar estado de servicios:

```bash
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo systemctl status mysql
```

## 14. Procedimiento de actualizacion (nuevas versiones)

1. Crear nuevo release y descargar codigo.
2. composer install --no-dev --optimize-autoloader
3. npm ci y npm run prod
4. enlazar .env y storage compartido
5. php artisan migrate --force
6. php artisan config:cache y view:cache
7. cambiar symlink current al nuevo release
8. reload nginx y restart php-fpm (si aplica)
9. smoke test rapido

## 15. Rollback (si algo falla)

1. Identificar release anterior estable.
2. Reapuntar symlink current al release previo.
3. Reload Nginx y (si aplica) restart PHP-FPM.
4. Verificar aplicacion.

Ejemplo:

```bash
ln -sfn /var/www/app-services-ula/releases/<RELEASE_ANTERIOR> /var/www/app-services-ula/current
sudo systemctl reload nginx
sudo systemctl restart php8.2-fpm
```

Nota:
- Si hubo migraciones destructivas, el rollback de codigo no revierte datos por si solo. Debe existir plan de backup/restore DB.

## 16. Backups y recuperacion

Recomendado diario:

1. Backup de base de datos (mysqldump o herramienta gestionada).
2. Backup de storage compartido.
3. Backup de shared .env (en vault seguro).
4. Prueba de restauracion al menos 1 vez al mes.

Ejemplo basico mysqldump:

```bash
mysqldump -u app_user -p app_services_ula > /backups/app_services_ula_$(date +%F).sql
```

## 17. Problemas frecuentes

1. Error 500 luego de deploy:
- Revisar storage/logs/laravel.log
- Revisar permisos de storage y bootstrap/cache

2. Error de assets:
- Confirmar npm run prod
- Confirmar public/js y public/css generados

3. Error de rutas cacheadas:
- No usar route:cache en este proyecto

4. Admin no existe:
- Ejecutar UserRoleSeeder y CreatePepitaAdmin

## 18. Evidencia minima de salida a produccion

Guardar en ticket de release:

1. Commit/tag desplegado.
2. Fecha/hora inicio-fin.
3. Checklist post despliegue firmado.
4. Resultado de pruebas de humo.
5. Ruta de backups previos.
6. Responsable tecnico y aprobador funcional.

---

Manual creado para uso operativo del proyecto app-services-ula.
Si el entorno es Docker, Azure App Service, o Kubernetes, crear anexo especifico con comandos y pipelines del entorno objetivo.

<!-- ACTUALIZACION_JUNIO_2026 -->
## Novedades Funcionales (Junio 2026)

- Carga masiva CSV reforzada con lectura UTF-8 y manejo explicito de comillas dobles como encapsulador de texto.
- Validacion estructural por fila en importaciones CSV para detectar columnas rotas por delimitador/comillas antes de escribir en BD.
- Mejora de importacion de cursos para relacion muchos-a-muchos con programas mediante tabla pivote course_program (manteniendo compatibilidad con program_id legado).
- Carga masiva de cursos con soporte de multiples referencias: program_id/program_ids, program_code/program_codes y program_name/program_names.
- Resolucion de ambiguedades de programas mejorada con filtros por faculty_id/faculty_name e institution_id/institution_name.
- Cuando program_code/program_name es duplicado y no se envia desambiguacion, la importacion puede vincular el curso a todos los programas coincidentes.
- Formularios de crear/editar cursos mejorados con selector multiple con busqueda (Tom Select), conservando compatibilidad del campo program_id.

