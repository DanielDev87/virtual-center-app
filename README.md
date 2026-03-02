# 🚀 A-DDIE - Sistema de Gestión y Colaboración Virtual para Centros de Producción y Medios

[![License: Apache 2.0](https://img.shields.io/badge/License-Apache_2.0-blue.svg)](https://opensource.org/licenses/Apache-2.0)
[![DOI](https://zenodo.org/badge/DOI/10.5281/zenodo.XXXXXXX.svg)](https://doi.org/10.5281/zenodo.XXXXXXX) <!-- Reemplaza con tu DOI real -->
[![Laravel 10](https://img.shields.io/badge/Laravel-10-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![Bootstrap 5](https://img.shields.io/badge/Bootstrap-5-7952B3?style=flat&logo=bootstrap)](https://getbootstrap.com)

## 📋 Descripción

**A-DDIE** es un sistema moderno de código abierto diseñado para la **gestión integral y colaboración virtual** en centros de producción y medios educativos. Desarrollado bajo los principios de la **Ciencia Abierta**, este software busca optimizar el flujo de trabajo en instituciones educativas, facilitando el seguimiento de proyectos, la gestión de colaboradores y la producción de contenido multimedia.

A-DDIE no es solo una herramienta, es una plataforma para la **transformación digital** de los entornos educativos, permitiendo una administración centralizada, eficiente y accesible. 


### Características Principales

-   🏢 **Gestión de Centros de Producción:** Administra recursos, proyectos y equipos en un solo lugar.
-   👥 **Colaboración Virtual:** Sistema de roles y permisos para equipos multidisciplinarios (docentes, estudiantes, productores).
-   📊 **Seguimiento de Proyectos:** Control de estado, tareas y cronogramas para producciones audiovisuales o multimedia.
-   🎨 **Interfaz Moderna y Accesible:** Desarrollado con Laravel 10 y Bootstrap 5, con soporte nativo para **modo oscuro/claro**.
-   🔌 **API REST:** Endpoints para integración con otras herramientas institucionales.
-   📱 **Responsive Design:** Accesible desde cualquier dispositivo, promoviendo la movilidad en los campus.


## 🛠️ Tecnologías Clave

-   **Backend:** Laravel 10 (PHP 8.1+)
-   **Frontend:** Bootstrap 5, JavaScript, AJAX
-   **Base de Datos:** MySQL
-   **Autenticación:** Laravel Sanctum


## Requisitos del Sistema 📦

- PHP 8.1 o superior
- Composer
- Node.js y NPM
- MySQL 5.7 o superior
- Servidor web (Apache/Nginx)

## Instalación ⚙️

### 1. Clonar el Proyecto

```bash
git clone <repository-url> virtual-center-app
cd virtual-center-app
```

### 2. Instalar Dependencias PHP

```bash
composer install
```

### 3. Instalar Dependencias Node.js

```bash
npm install
```

### 4. Configurar Variables de Entorno

```bash
cp env.example .env
```

Editar el archivo `.env` con tus configuraciones:

```env
APP_NAME="Virtual Center"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=virtual_center_db
DB_USERNAME=root
DB_PASSWORD=tu_password
```

### 5. Generar Clave de Aplicación

```bash
php artisan key:generate
```

### 6. Ejecutar Migraciones

```bash
php artisan migrate
```

### 7. Compilar Assets

```bash
npm run dev
```

Para producción:
```bash
npm run production
```

### 8. Configurar Servidor Web

#### Apache (.htaccess)
```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

#### Nginx
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

## Estructura del Proyecto 📁

```
virtual-center-app/
├── app/
│   ├── Http/Controllers/     # Controladores
│   ├── Models/              # Modelos Eloquent
│   └── Providers/           # Proveedores de servicios
├── database/
│   └── migrations/          # Migraciones de base de datos
├── resources/
│   ├── views/              # Vistas Blade
│   ├── css/                # Estilos CSS
│   └── js/                 # JavaScript
├── routes/
│   ├── web.php            # Rutas web
│   └── api.php            # Rutas API
└── public/                # Archivos públicos
```

## Base de Datos

### Tablas Principales

- `institutions` - Instituciones
- `material_types` - Tipos de material
- `user_roles` - Roles de usuario
- `users` - Usuarios del sistema
- `project_tracking` - Seguimiento de proyectos
- `project_comments` - Comentarios de proyectos
- `task_lists` - Lista de tareas
- `mediation_forms` - Formularios de mediación
- `form_html_descriptions` - Descripciones HTML

### Migración desde el Sistema Anterior

Para migrar datos desde el sistema anterior, ejecuta:

```bash
php artisan migrate:from-legacy
```

## Uso del Sistema 💻

### Acceso Principal

- **URL**: `http://localhost`
- **Login**: `/login`
- **Dashboard**: `/dashboard` (requiere autenticación)

### Módulos Principales

1. **Inicio** - Página principal con información general
2. **Colaboradores** - Gestión de usuarios y perfiles
3. **Gestión de Servicios** - Administración de proyectos
4. **Emisora** - Micrositio de radio
5. **Monitor** - Panel de monitoreo y analytics

### Modo Oscuro/Claro

El sistema incluye un botón de toggle en la barra de navegación para cambiar entre modo claro y oscuro. La preferencia se guarda en la sesión del usuario.

## Desarrollo 🧑‍💻

### Comandos Útiles

```bash
# Servidor de desarrollo
php artisan serve

# Compilar assets en tiempo real
npm run watch

# Ejecutar tests
php artisan test

# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Estructura de Vistas

Las vistas utilizan el sistema de plantillas Blade de Laravel con un layout principal (`layouts/app.blade.php`) que incluye:

- Navbar responsive
- Toggle de tema
- Footer
- Scripts y estilos

### API Endpoints

- `GET /api/user` - Información del usuario autenticado
- `POST /api/theme` - Guardar preferencia de tema
- `GET /ajax/search` - Búsqueda de proyectos
- `GET /ajax/project-details/{id}` - Detalles de proyecto
- `POST /ajax/send-status` - Actualizar estado de proyecto

## Personalización 🎨

### Colores y Temas

Los colores principales se definen en `resources/css/app.css`:

```css
:root {
    --vc-primary: #0280AE;
    --vc-secondary: #6c757d;
    --vc-accent: #17a2b8;
}
```

### Configuración de Roles

Los roles de usuario se configuran en la tabla `user_roles` y se pueden personalizar según las necesidades del proyecto.

## Seguridad 🔒

- Autenticación con Laravel Sanctum
- Protección CSRF en formularios
- Validación de datos de entrada
- Sanitización de salida
- Rate limiting en API

## Contribución 🤝

1. Fork el proyecto
2. Crear una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -am 'Agregar nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Crear un Pull Request

## Licencia ⚖️ 

Este proyecto está bajo los principios de la Ciencia Abierta y utiliza la licencia Apache 2.0. Esto significa que eres libre de:

✅ Usarlo para fines comerciales y académicos.
✅ Modificarlo y adaptarlo a tus necesidades.
✅ Distribuir copias.
✅ Usar sus patentes (si las hubiera).
No se proporciona garantía alguna, tal como estipula la licencia. Para más detalles, consulta el archivo LICENSE en la raíz del proyecto.

## Soporte Y Contacto📞

Para soporte técnico, preguntas o reportar errores, por favor contacta al equipo de desarrollo:

- Desarrollador Principal: Daniel Agudelo
- Repositorio Oficial: https://github.com/DanielDev87
- Reportar Issues: [Link a la sección de Issues de tu GitHub]

---

**A-DDIE** - Sistema de Gestión y Colaboración Virtual
Impulsando la Ciencia Abierta y la transformación digital en la educación.
Desarrollado con ❤️ por Daniel Agudelo usando Laravel y Bootstrap.


