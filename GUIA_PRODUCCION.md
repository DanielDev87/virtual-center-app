# Guia de Produccion - app-services-ula

Esta guia describe un despliegue robusto para Laravel 10 en entorno productivo.

Alcance:
- Servidor Linux (Ubuntu 22.04/24.04)
- Nginx + PHP-FPM + MySQL/MariaDB
- Build de assets con Laravel Mix
- Operacion segura (logs, backups, monitoreo, colas)

## 1. Requisitos tecnicos de produccion

- CPU: 2 vCPU minimo (4 recomendado)
- RAM: 4 GB minimo (8 GB recomendado)
- Disco: 40 GB minimo SSD
- SO: Ubuntu LTS
- PHP: 8.2 o superior (el proyecto exige ^8.2)
- Node.js: 18 o superior
- Composer 2.x
- MySQL 8 o MariaDB 10.6+

## 2. Topologia recomendada

- 1 servidor web/app (Nginx + PHP-FPM)
- 1 servidor de base de datos (ideal separado)
- HTTPS con certificado valido
- Backups en almacenamiento externo

## 3. Preparacion del servidor

Instalar paquetes base:

    sudo apt update
    sudo apt install -y nginx mysql-server unzip git curl supervisor

Instalar PHP 8.2 y extensiones Laravel:

    sudo apt install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-bcmath php8.2-intl php8.2-gd php8.2-fileinfo

Instalar Composer:

    cd /tmp
    curl -sS https://getcomposer.org/installer -o composer-setup.php
    php composer-setup.php
    sudo mv composer.phar /usr/local/bin/composer

Instalar Node.js 18 (ejemplo con NodeSource):

    curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
    sudo apt install -y nodejs

## 4. Crear usuario de despliegue y carpeta de app

    sudo adduser --disabled-password --gecos "" deploy
    sudo usermod -aG www-data deploy
    sudo mkdir -p /var/www/app-services-ula
    sudo chown -R deploy:www-data /var/www/app-services-ula

## 5. Obtener codigo

    sudo -u deploy -H bash
    cd /var/www/app-services-ula
    git clone <URL_DEL_REPO> .

## 6. Configurar entorno (.env)

Crear archivo .env desde env.example:

    cp env.example .env

Configurar valores productivos minimos:

    APP_NAME="Mesa de Servicio"
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
    DB_PASSWORD=clave_segura

    CACHE_DRIVER=file
    SESSION_DRIVER=file
    QUEUE_CONNECTION=sync

    SESSION_SECURE_COOKIE=true

Notas:
- SESSION_SECURE_COOKIE=true requiere HTTPS.
- Si activas Redis en produccion, migra CACHE_DRIVER/SESSION_DRIVER/QUEUE_CONNECTION a redis.

## 7. Base de datos

Crear DB y usuario con privilegios limitados:

    sudo mysql

    CREATE DATABASE app_services_ula CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    CREATE USER 'app_user'@'127.0.0.1' IDENTIFIED BY 'clave_segura';
    GRANT ALL PRIVILEGES ON app_services_ula.* TO 'app_user'@'127.0.0.1';
    FLUSH PRIVILEGES;
    EXIT;

## 8. Instalacion de dependencias y build

Desde /var/www/app-services-ula:

    composer install --no-dev --optimize-autoloader
    npm ci
    npm run prod

## 9. Inicializacion Laravel

    php artisan key:generate --force
    php artisan migrate --force

Seed recomendado para ambiente limpio sin legado:

    php artisan db:seed --class=UserRoleSeeder --force
    php artisan db:seed --class=CreatePepitaAdmin --force

## 10. Optimizaciones Laravel

Ejecutar:

    php artisan config:cache
    php artisan view:cache

Importante para este proyecto:
- No ejecutar route:cache.
- El archivo routes/web.php contiene rutas con closures (ejemplo tema AJAX y fallback), lo cual rompe route:cache.

## 11. Permisos

Ajustar permisos de escritura:

    sudo chown -R deploy:www-data /var/www/app-services-ula
    sudo find /var/www/app-services-ula -type f -exec chmod 664 {} \;
    sudo find /var/www/app-services-ula -type d -exec chmod 775 {} \;
    sudo chmod -R 775 /var/www/app-services-ula/storage
    sudo chmod -R 775 /var/www/app-services-ula/bootstrap/cache

Enlace storage:

    php artisan storage:link

## 12. Configurar Nginx

Crear /etc/nginx/sites-available/app-services-ula:

    server {
        listen 80;
        server_name tu-dominio.com;
        root /var/www/app-services-ula/public;

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

Activar sitio:

    sudo ln -s /etc/nginx/sites-available/app-services-ula /etc/nginx/sites-enabled/
    sudo nginx -t
    sudo systemctl reload nginx

## 13. HTTPS (Let's Encrypt)

    sudo apt install -y certbot python3-certbot-nginx
    sudo certbot --nginx -d tu-dominio.com

Verificar renovacion:

    sudo certbot renew --dry-run

## 14. PHP-FPM ajustes recomendados

Archivo orientativo: /etc/php/8.2/fpm/pool.d/www.conf

- pm = dynamic
- pm.max_children = 30 (ajustar segun RAM)
- pm.start_servers = 4
- pm.min_spare_servers = 2
- pm.max_spare_servers = 8

Luego:

    sudo systemctl restart php8.2-fpm

## 15. Colas (opcional recomendado)

Si mantienes QUEUE_CONNECTION=sync, no necesitas workers.
Si cambias a database o redis:

1) Generar tablas necesarias (si no existen):

    php artisan queue:table
    php artisan queue:failed-table
    php artisan migrate --force

2) Configurar Supervisor en /etc/supervisor/conf.d/laravel-worker.conf:

    [program:laravel-worker]
    process_name=%(program_name)s_%(process_num)02d
    command=php /var/www/app-services-ula/artisan queue:work --sleep=3 --tries=3 --timeout=120
    autostart=true
    autorestart=true
    stopasgroup=true
    killasgroup=true
    user=deploy
    numprocs=2
    redirect_stderr=true
    stdout_logfile=/var/www/app-services-ula/storage/logs/worker.log

3) Aplicar:

    sudo supervisorctl reread
    sudo supervisorctl update
    sudo supervisorctl start laravel-worker:*

## 16. Scheduler (si se usa)

Agregar cron:

    * * * * * cd /var/www/app-services-ula && php artisan schedule:run >> /dev/null 2>&1

## 17. Logs y monitoreo

Revisar logs:

- Laravel: storage/logs/laravel.log
- Nginx access/error logs
- PHP-FPM logs

Comandos utiles:

    php artisan optimize:clear
    php artisan about
    php artisan test

## 18. Backups

Minimo recomendado:
- Backup diario de base de datos
- Backup diario de carpeta storage/app y .env (cifrado)
- Retencion 7/30/90 dias
- Almacenamiento externo

Dump ejemplo:

    mysqldump -u app_user -p app_services_ula > /backups/app_services_ula_$(date +%F).sql

## 19. Flujo de despliegue sin downtime (basico)

Secuencia sugerida:

1) git pull
2) composer install --no-dev --optimize-autoloader
3) npm ci && npm run prod
4) php artisan migrate --force
5) php artisan config:cache
6) php artisan view:cache
7) php artisan queue:restart (si aplica)
8) sudo systemctl reload php8.2-fpm
9) Verificacion funcional

## 20. Checklist final de go-live

- APP_ENV=production y APP_DEBUG=false
- HTTPS activo y renovacion OK
- Migraciones aplicadas
- Build frontend en modo prod
- Permisos correctos en storage y bootstrap/cache
- Health-check manual de login, dashboard y creacion/seguimiento de ticket
- Backups automatizados
- Monitoreo y alertas activos

## 21. Rollback rapido

Si falla despliegue:

1) Volver al commit/tag anterior
2) Restaurar assets compilados anteriores
3) Restaurar DB desde backup solo si hubo migraciones destructivas
4) limpiar cache:

    php artisan optimize:clear

5) recachear config/vistas:

    php artisan config:cache
    php artisan view:cache

---



<!-- ACTUALIZACION_JUNIO_2026 -->
## Novedades Funcionales (Junio 2026)

- Carga masiva CSV reforzada con lectura UTF-8 y manejo explicito de comillas dobles como encapsulador de texto.
- Validacion estructural por fila en importaciones CSV para detectar columnas rotas por delimitador/comillas antes de escribir en BD.
- Mejora de importacion de cursos para relacion muchos-a-muchos con programas mediante tabla pivote course_program (manteniendo compatibilidad con program_id legado).
- Carga masiva de cursos con soporte de multiples referencias: program_id/program_ids, program_code/program_codes y program_name/program_names.
- Resolucion de ambiguedades de programas mejorada con filtros por faculty_id/faculty_name e institution_id/institution_name.
- Cuando program_code/program_name es duplicado y no se envia desambiguacion, la importacion puede vincular el curso a todos los programas coincidentes.
- Formularios de crear/editar cursos mejorados con selector multiple con busqueda (Tom Select), conservando compatibilidad del campo program_id.

