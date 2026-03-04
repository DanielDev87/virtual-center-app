# Virtual Center - Sistema de Gestión y Colaboración Virtual

## Descripción

Virtual Center es un sistema moderno de gestión y colaboración virtual para la centros de produccion y medios en instituciones educativas desarrollado con Laravel 10, Bootstrap 5 y características avanzadas como modo oscuro/claro. 

## Características Principales

- ✅ **Laravel 10** - Framework PHP moderno y robusto
- ✅ **Bootstrap 5** - Interfaz de usuario moderna y responsive
- ✅ **Modo Oscuro/Claro** - Tema personalizable con persistencia
- ✅ **Base de Datos Renombrada** - Estructura limpia sin referencias UCC/UCN
- ✅ **Autenticación Integrada** - Sistema de login seguro
- ✅ **Gestión de Proyectos** - Seguimiento completo de proyectos
- ✅ **Sistema de Colaboradores** - Gestión de usuarios y roles
- ✅ **API REST** - Endpoints para funcionalidades AJAX
- ✅ **Responsive Design** - Compatible con dispositivos móviles

## Requisitos del Sistema

- PHP 8.1 o superior
- Composer
- Node.js y NPM
- MySQL 5.7 o superior
- Servidor web (Apache/Nginx)

## Instalación

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

## Estructura del Proyecto

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

## Uso del Sistema

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

## Desarrollo

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

## Personalización

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

## Seguridad

- Autenticación con Laravel Sanctum
- Protección CSRF en formularios
- Validación de datos de entrada
- Sanitización de salida
- Rate limiting en API

## Contribución

1. Fork el proyecto
2. Crear una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -am 'Agregar nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Crear un Pull Request

## Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## Soporte

Para soporte técnico o preguntas, contacta al equipo de desarrollo.

---

**Virtual Center** - Sistema de Gestión y Colaboración Virtual
Desarrollado con ❤️ por Daniel Agudelo usando Laravel y Bootstrap

----------------------------------
cuando un administrador quiera marcar como terminado un servicio, deberá relacionar un enlace al recurso generado, sino no podrá marcarlo como cerrado, adicionalmente un servicio podrá ser reabierto por el administrador a solicitud del solicitante y volverse a registar avances en una nueva seccion que solo se mostrará cuando un servicio haya sido reabierto, al completarlo nuevamente se habilitará la opcion de calificar. Que representa la evaluación de ADDIE. Ningun ticket podrá cerrarse hasta que la barra de vance se halla completado al 100% y halla pasado ´por todas las fases de ADDIE.

Se rompio la opcion de actualiar contraseñas de un usuario desde administrador

Hashear las rutas en el navegador para que no se pueda acceder a ellas directamente

crear un api para que una app movil pueda acceder a los datos del sistema


