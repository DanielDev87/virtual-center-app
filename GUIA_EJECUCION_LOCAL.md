# Guia de ejecucion local - app-services-ula

Esta guia te permite levantar el proyecto en Windows de forma estable.

## 1) Requisitos

- PHP 8.2 o superior
- Composer
- Node.js + npm
- MySQL o MariaDB

Nota: el proyecto exige PHP `^8.2` segun `composer.json`.

## 2) Abrir el proyecto

En PowerShell:

```powershell
Set-Location "C:\Users\jean.montoyaca\Daniel\proyectosDaniel\app-services-ula"
```

## 3) Instalar dependencias

```powershell
composer install
npm install
```

## 4) Configurar entorno

Crear archivo `.env` desde el ejemplo:

```powershell
Copy-Item .\env.example .\.env
```

Editar `.env` y ajustar minimo:

```env
APP_NAME="Virtual Center"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=virtual_center_db
DB_USERNAME=root
DB_PASSWORD=
```

## 5) Crear base de datos

En MySQL/MariaDB crea la BD indicada en `DB_DATABASE` (por ejemplo `virtual_center_db`).

## 6) Inicializar Laravel

```powershell
php artisan key:generate
php artisan migrate
```

## 7) Seed recomendado para instalacion limpia

En este proyecto, el seeder principal intenta migrar usuarios legacy.
Para una instalacion local limpia, usa estos seeders puntuales:

```powershell
php artisan db:seed --class=UserRoleSeeder
php artisan db:seed --class=CreatePepitaAdmin
```

Credenciales admin de prueba creadas:

- Email: `pepita@prueba.edu.co`
- Password: `password`

## 8) Compilar assets frontend

Para compilar una vez:

```powershell
npm run dev
```

Para modo observacion durante desarrollo:

```powershell
npm run watch
```

## 9) Levantar servidor

```powershell
php artisan serve
```

Abrir en navegador:

- `http://127.0.0.1:8000`
- Login: `http://127.0.0.1:8000/login`

## 10) Verificacion rapida

- Carga la home
- Puedes iniciar sesion con Pepita
- El panel admin responde sin errores

## 11) Comandos utiles

Ejecutar tests:

```powershell
php artisan test
```

Limpiar cache/config/rutas/vistas:

```powershell
php artisan optimize:clear
```

Regenerar autoload de Composer:

```powershell
composer dump-autoload
```

## 12) Solucion de problemas comunes

### Error de version PHP en Composer

- Verifica version:

```powershell
php -v
```

- Debe ser 8.2 o superior.

### Error de conexion a base de datos

- Revisa valores `DB_*` en `.env`.
- Verifica que el motor MySQL/MariaDB este encendido.
- Confirma que la BD exista.

### Error al hacer seed global

Si falla `php artisan db:seed`, usa el flujo de seed limpio del paso 7.

### Cambios de frontend no visibles

- Ejecuta `npm run dev` o `npm run watch`.
- Fuerza recarga del navegador (Ctrl + F5).

<!-- ACTUALIZACION_JUNIO_2026 -->
## Novedades Funcionales (Junio 2026)

- Carga masiva CSV reforzada con lectura UTF-8 y manejo explicito de comillas dobles como encapsulador de texto.
- Validacion estructural por fila en importaciones CSV para detectar columnas rotas por delimitador/comillas antes de escribir en BD.
- Mejora de importacion de cursos para relacion muchos-a-muchos con programas mediante tabla pivote course_program (manteniendo compatibilidad con program_id legado).
- Carga masiva de cursos con soporte de multiples referencias: program_id/program_ids, program_code/program_codes y program_name/program_names.
- Resolucion de ambiguedades de programas mejorada con filtros por faculty_id/faculty_name e institution_id/institution_name.
- Cuando program_code/program_name es duplicado y no se envia desambiguacion, la importacion puede vincular el curso a todos los programas coincidentes.
- Formularios de crear/editar cursos mejorados con selector multiple con busqueda (Tom Select), conservando compatibilidad del campo program_id.

