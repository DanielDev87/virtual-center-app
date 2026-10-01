# Iconos Font Awesome para tópicos

> Los iconos usan la clase de [Font Awesome 5 / 6](https://fontawesome.com/icons) y se pueden usar directamente con `<i class="[clase]"></i>`.

| Tópico | Departamento | SLA | Clase FA | Previsualización |
|--------|-------------|-----|----------|-----------------|
| Acceso Sistema Académico (Estudiantes) | Registro Académico | 48 horas | `fas fa-user-graduate` | 🎓 |
| Gestión de Correo Institucional | Infraestructura Tecnológica - TIC | 48 horas | `fas fa-envelope-open` | 📬 |
| Soporte Infraestructura Tecnológica - TIC | Infraestructura Tecnológica - TIC | 48 horas | `fas fa-server` | 🖥 |
| Educación Virtual | Depto. Educación Virtual y a Distancia | 48 horas | `fas fa-chalkboard-teacher` | 📡 |
| Centro Regional Apartadó | Centro Regional Apartadó | — | `fas fa-map-marker-alt` | 📍 |
| Gestión Intranet | Web Master | 48 horas | `fas fa-globe` | 🌐 |
| Servicios Generales | Servicios Generales | 48 horas | `fas fa-concierge-bell` | 🔔 |
| Solicitud de Reportes - SUI | Departamento SUI | 48 horas | `fas fa-chart-bar` | 📊 |
| Aulas Virtuales - Cursos de TIC | Cursos TIC | 48 horas | `fas fa-desktop` | 🖥 |
| Cursos Administración Distancia | Admon. de Empresas Distancia | 48 horas | `fas fa-briefcase` | 💼 |
| Aulas Virtuales - Cursos de AFI | Cursos de AFI (aulas virtuales) | 48 horas | `fas fa-video` | 🎬 |
| Mantenimiento Planta Física y Vigilancia | Mantenimiento Planta Física y Vigilancia | — | `fas fa-hard-hat` | 🪖 |
| Gestión Sistema de Control de Acceso | Infraestructura Tecnológica - TIC | 48 horas | `fas fa-shield-alt` | 🛡 |
| Soporte SUI - SW para la Operación | Departamento SUI | 48 horas | `fas fa-cogs` | ⚙️ |
| Gestión Activos Fijos | — | — | `fas fa-boxes` | 📦 |
| Acceso a Turnitin - Docentes | Infraestructura Tecnológica - TIC | 48 horas | `fas fa-file-contract` | 📄 |
| Solicitudes Habeas Data | Área de Habeas Data | — | `fas fa-user-shield` | 🔒 |
| Solicitud de Información - Dirección de Planeación | Dirección de Planeación | — | `fas fa-clipboard-list` | 📋 |
| Acceso Sistema Académico (Docentes y Administrativos) | Gestión Humana | — | `fas fa-id-card` | 🪪 |
| Soporte Salas de Sistemas y Medios Digitales | Infraestructura Tecnológica - TIC | 48 horas | `fas fa-laptop` | 💻 |

## Uso en HTML

```html
<!-- Ejemplo con Bootstrap + Font Awesome 6 -->
<i class="fas fa-user-graduate me-2"></i> Acceso Sistema Académico (Estudiantes)
```

## Criterios de selección

| Icono | Criterio |
|-------|---------|
| `fa-user-graduate` / `fa-id-card` | Acceso a sistemas académicos diferenciado por actor (estudiante vs. docente/admin) |
| `fa-envelope-open` | Gestión de correo |
| `fa-server` | Infraestructura física de red y servidores |
| `fa-chalkboard-teacher` | Educación virtual / docencia |
| `fa-globe` | Intranet / web |
| `fa-concierge-bell` | Servicios generales / atención |
| `fa-chart-bar` | Reportes y estadísticas |
| `fa-desktop` / `fa-laptop` | Salas de sistemas y aulas virtuales de TIC |
| `fa-briefcase` | Administración y cursos de empresas |
| `fa-video` | Cursos con aulas virtuales multimedia |
| `fa-hard-hat` | Mantenimiento y obras físicas |
| `fa-shield-alt` | Control de acceso y seguridad |
| `fa-cogs` | Software operacional / soporte técnico avanzado |
| `fa-boxes` | Activos y bienes físicos |
| `fa-file-contract` | Antiplagio / documentos académicos |
| `fa-user-shield` | Privacidad y protección de datos |
| `fa-clipboard-list` | Solicitudes de información / planeación |
| `fa-map-marker-alt` | Sedes y centros regionales |


<!-- ACTUALIZACION_JUNIO_2026 -->
## Novedades Funcionales (Junio 2026)

- Carga masiva CSV reforzada con lectura UTF-8 y manejo explicito de comillas dobles como encapsulador de texto.
- Validacion estructural por fila en importaciones CSV para detectar columnas rotas por delimitador/comillas antes de escribir en BD.
- Mejora de importacion de cursos para relacion muchos-a-muchos con programas mediante tabla pivote course_program (manteniendo compatibilidad con program_id legado).
- Carga masiva de cursos con soporte de multiples referencias: program_id/program_ids, program_code/program_codes y program_name/program_names.
- Resolucion de ambiguedades de programas mejorada con filtros por faculty_id/faculty_name e institution_id/institution_name.
- Cuando program_code/program_name es duplicado y no se envia desambiguacion, la importacion puede vincular el curso a todos los programas coincidentes.
- Formularios de crear/editar cursos mejorados con selector multiple con busqueda (Tom Select), conservando compatibilidad del campo program_id.

