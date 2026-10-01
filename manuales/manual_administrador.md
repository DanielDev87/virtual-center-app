# Manual de Usuario - Administrador
## Sistema Virtual Center

---

## Tabla de Contenidos

1. [Introducción](#introducción)
2. [Acceso al Sistema](#acceso-al-sistema)
3. [Dashboard Principal](#dashboard-principal)
4. [Gestión de Tickets](#gestión-de-tickets)
5. [Gestión de Usuarios](#gestión-de-usuarios)
6. [Gestión de Roles](#gestión-de-roles)
7. [Gestión de Puestos de Trabajo](#gestión-de-puestos-de-trabajo)
8. [Project Management (Metodología ADDIE + SCRUM)](#project-management)
9. [Módulo de Reportes](#módulo-de-reportes)
10. [Configuración del Sistema Institucional](#configuración-del-sistema-institucional)
11. [Configuración y Roles Especiales Técnicos](#configuración-y-roles-especiales-técnicos)

---

## Introducción

### ¿Qué es Virtual Center?

Virtual Center es un sistema de gestión de servicios educativos que integra la metodología ADDIE (Analysis, Design, Development, Implementation, Evaluation) con prácticas SCRUM para la gestión efectiva de proyectos y tickets de soporte de la institución.

### Rol de Administrador

Como administrador, tienes acceso de control y gestión con las siguientes responsabilidades:

- Gestionar todos los tickets del sistema
- Administrar usuarios y roles
- Asignar colaboradores a tickets de manera dinámica
- Asignar únicamente usuarios que pertenezcan al equipo configurado del tópico
- Revisar alertas de tickets devueltos por Operarios en el dashboard
- Monitorear el progreso en conjunto de los proyectos
- Generar reportes y métricas

---

## Acceso al Sistema

### Inicio de Sesión

1. Accede a la URL del sistema Virtual Center (ej. `/login`).
2. Ingresa tu **correo electrónico** y **contraseña**.
3. Haz clic en **"Iniciar Sesión"**.

El panel de administración cuenta con un **Sidebar izquierdo** fijo desde el que controlas todas tus opciones, una **Barra superior** para tu perfil y el **Área central** del módulo seleccionado.

---

## Dashboard Principal

El dashboard expone parámetros clave para tu evaluación visual de carga de la mesa de servicio:

- **Estadísticas Generales**: Total de tickets, listados pendientes, tickets en progreso y tickets finalizados.
- **Métricas de Rendimiento**: Promedio de calificaciones, número de usuarios y roles activos.
- **Gráficos**: Incluye distribuciones representadas en gráficas de torta sobre el cumplimiento de tiempos, así como el Chart global de calificaciones del sistema (1 a 5 estrellas).
- **Tablas de tickets con alertas**: Seguimientos urgentes que demanden la atención del Project Manager.

---

## Gestión de Tickets

### Visualizar Lista de Tickets

**Ruta**: `Admin > Tickets`

La lista te presentará tickets paginados por estado (Pendiente, En Progreso, Realizado por Operario, Completado, Cancelado). Tendrás accesos en tiempo real para visualizar qué tópicos se están gestionando y qué porcentaje acumulado dictamina el sistema.

### Ver Detalles de un Ticket e Intervenir Equipo

Haz clic en **"Ver"** en un ticket para gestionar su asignación:

1. El sistema soporta un entorno **Multi-Mediador**. En la sección "Equipo de Trabajo", solo se listan usuarios activos pertenecientes al equipo del tópico correspondiente. Puedes agregar varios colaboradores u operarios cuando el trabajo lo requiera.
2. Es posible asignar o prescindir de un `Puesto de Trabajo` rígido. La asignación es flexible según la carga de trabajo.

### Establecer Entorno de Cierre y Políticas

#### Requisitos para Cerrar como "Completado"
El cierre oficial puede realizarlo el Admin o un Contributor según el flujo aplicable. Un Operario nunca marca un ticket como Completado: solo puede marcarlo como **Realizado** para que el Admin Área lo audite. El sistema validará de manera **estricta** lo siguiente:
1. ✅ **Progreso en 100%**: El indicador debe estar llenado en su totalidad.
2. ✅ **Fases de Virtual Center (ADDIE)**: El ticket solamente estará habilitado para cierre si es categorizado bajo la fase "Implementation" o "Evaluation".
3. ✅ **Evidencias o Recurso**: Ya sea en comentarios de texto enriquecido o enlace del recurso final.

### Reabrir un Ticket
Si el trabajo ha de ser replanteado porque el usuario expuso disconformidades:
1. Accede al ticket cerrado y selecciona **"Reabrir Ticket"**.
2. Los progresos de 100% se moverán al histórico como "Avances Anteriores".
3. El tracking comenzará desde 0%, eliminándose su calificación en orden de exigir una más representativa a la hora de completarlo una segunda vez.

### Auditoría de tickets realizados por Operarios

Cuando un Operario marca un ticket como **Realizado**, el ticket queda en estado de revisión. El Admin Área puede revisar la descripción y las evidencias, corregir el detalle de solución y:

- **Aprobar y completar**: cambia el estado a Completado y notifica al solicitante por correo.
- **Devolver para corrección**: cambia el estado a En Proceso y registra el motivo.

Los tickets Completados o Cancelados no permiten asignar mediadores, remover miembros, cambiar prioridad ni ejecutar otras modificaciones hasta que sean reabiertos.

---

## Gestión de Usuarios

**Ruta**: `Admin > Usuarios`

### Crear o Editar

Al gestionar perfiles, asegúrate de mantener actualizados los nuevos campos:
- **Correo y Contraseña**.
- **Roles principales**: Admin, Monitor, Contributor, Operario, Requester, Super Admin Tecnico y Admin Área.
- **Información complementaria**: Link o documento adicional referencial.
- **Área Institucional**: Especifica al área a la cual obedece el nuevo usuario dentro de la universidad/entidad.

---

## Gestión de Roles y Tipos de Solicitud (Tópicos)

### Tipos de Solicitud

**Ruta**: `Admin > Request Types (Tipos de Solicitud)`

Para cada tipo de servicio dentro de Virtual Center, se configura la matriz base. Los nuevos Request Types exigen:
- **Departamento y Área**: Ramificación de la organización.
- **SLA (Acuerdo de nivel de servicio)**: Límites de tiempo preestablecidos y categorizados por el sistema.
- **Gestor Principal (Manager)**: Funcionario encargado que sirve de garante para ese tipo de solicitud.

---

## Configuración del Sistema Institucional

La plataforma requiere que estructures adecuadamente la malla académica/organizativa para el correcto escalonamiento.

- **Departamentos y Áreas**: (`Admin > Departments` y `Admin > Areas`). Sirven para clasificar la rama estructural y administrativa, siendo utilizados en los perfiles de los usuarios y tipos de solicitudes.
- **Facultades y Programas**: Clasificación de la rama académica desde donde provienen las solicitudes de recursos estudiantiles/profesorales.

---

## Project Management

**Ruta**: Dentro del detalle de cada ticket individual, hay un botón hacia el **"Project Dashboard"**.

El Dashboard te permite desglosar metodológicamente las estrategias. Las tareas del Kanban y Sprints de SCRUM conviven allí. No interfieren con el porcentaje principal general si no se desea, pero son vitales para tickets prolongados manejados por equipos con más de 3 contributors.

Podrás modificar manualmente la **Fase ADDIE** actual (de Analysis a Evaluation) dictaminando si el proyecto avanza o sufre atrasos operacionales.

---

## Módulo de Reportes

**Ruta**: `Admin > Reportes`

A través del panel podrás generar volcados totales para Microsoft Excel y otros sistemas empresariales, usando formatos CSV para:
- Reportes Globales de Tickets.
- Rendimiento analítico individual de Colaboradores.
- Registros absolutos segmentados por avances de proyectos y periodos de fechas.

---

## Configuración y Roles Especiales Técnicos

Virtual Center incluye un rol avanzado e independiente para mantenimientos operacionales, y debe dársele extremo cuidado a su asignación:

### Rol: "Super Admin Tecnico"
A diferencia de un Admin regular, el **Super Admin Tecnico** tiene la facultad de ingresar a ajustes sistémicos críticos (`technical/storage-settings`). 

Desde esta plataforma puede:
- Modificar lógicas de guardado y mapeo de **almacenamiento de evidencias físicas**, manipulando las constantes del diccionario virtual (App Settings y Store Paths). 
- Solo debe ser configurado por personal de TI/SysAdmin que coordine el servidor del sistema.

---

## Soporte y Preguntas Frecuentes

### ¿Puede un Contribuidor cerrar un ticket?
Sí, en la actualización moderna de Virtual Center, los Colaboradores (Contributors) pueden auto-cerrar sus propios proyectos una vez han adjuntado todo lo necesario y el sistema valido las condiciones lógicas de metodología ADDIE y porcentaje de avance. 

### ¿Necesito obligatoriamente un "Puesto de Trabajo" para delegar?
No. La versión reciente estipula que el "Puesto" es indicativo pero *no restrictivo* (puede ser nulo), con fines informativos para coordinar las labores complejas. 

### Contacto

Para asistencia técnica:
- **Email**: correo@institucion.edu.co

---

**Versión del Manual**: 1.1  
**Última Actualización**: Abril 2026  
**Sistema**: Virtual Center v1.1

<!-- ACTUALIZACION_JUNIO_2026 -->
## Novedades Funcionales (Junio 2026)

- Carga masiva CSV reforzada con lectura UTF-8 y manejo explicito de comillas dobles como encapsulador de texto.
- Validacion estructural por fila en importaciones CSV para detectar columnas rotas por delimitador/comillas antes de escribir en BD.
- Mejora de importacion de cursos para relacion muchos-a-muchos con programas mediante tabla pivote course_program (manteniendo compatibilidad con program_id legado).
- Carga masiva de cursos con soporte de multiples referencias: program_id/program_ids, program_code/program_codes y program_name/program_names.
- Resolucion de ambiguedades de programas mejorada con filtros por faculty_id/faculty_name e institution_id/institution_name.
- Cuando program_code/program_name es duplicado y no se envia desambiguacion, la importacion puede vincular el curso a todos los programas coincidentes.
- Formularios de crear/editar cursos mejorados con selector multiple con busqueda (Tom Select), conservando compatibilidad del campo program_id.

<!-- ACTUALIZACION_SEPTIEMBRE_2026 -->
## Funcionalidades operativas y de calidad

Para el alcance actualizado de roles, SLA, asociaciones, incidencias y reportes consulta [actualizacion_funcionalidades_2026.md](actualizacion_funcionalidades_2026.md).

- El Admin Área puede activar incidencias generales por tópico.
- Contributor, Admin y Admin Área pueden asociar tickets relacionados.
- Contributors envían solicitudes agrupadas de asociación para aprobación del Admin Área.
- El cierre de un ticket principal puede propagar la respuesta, estado y notificación a sus tickets asociados.
- El sistema calcula SLA con jornada laboral colombiana y festivos registrados.
- El reporte de solicitantes frecuentes permite identificar demanda recurrente.

