# Manual General de Uso - Mesa de Servicio
## Universidad Católica Luis Amigó

**Versión**: 1.0  
**Última Actualización**: Mayo 2026  
**Sistema**: Mesa de Servicio v1.1

---

## Tabla de Contenidos

1. [Introducción](#introducción)
2. [Acceso al Sistema](#acceso-al-sistema)
3. [Roles del Sistema](#roles-del-sistema)
4. [Guía por Rol](#guía-por-rol)
   - [Solicitante](#solicitante)
   - [Colaborador](#colaborador)
   - [Administrador](#administrador)
   - [Monitor / Auditor](#monitor--auditor)
   - [Super Admin Técnico](#super-admin-técnico)
5. [Funcionalidades Comunes](#funcionalidades-comunes)
6. [Metodología ADDIE](#metodología-addie)
7. [Preguntas Frecuentes](#preguntas-frecuentes)
8. [Soporte](#soporte)

---

## Introducción

### ¿Qué es la Mesa de Servicio?

La **Mesa de Servicio Universidad Católica Luis Amigó** es un sistema de gestión integral de tickets y proyectos educativos. Permite solicitar servicios, hacer seguimiento en tiempo real, colaborar en equipo y generar reportes de rendimiento, todo bajo la metodología **ADDIE** (Analysis, Design, Development, Implementation, Evaluation).

### Características Principales

- Creación y seguimiento de tickets de servicio
- Asignación multi-colaborador con roles flexibles
- Registro de avances con texto enriquecido e imágenes
- Sistema de calificación y retroalimentación
- Reportes exportables a Excel y CSV
- Seguimiento público de tickets (sin necesidad de login)
- Modo oscuro/claro

---

## Acceso al Sistema

### Inicio de Sesión

1. Accede a la URL del sistema (ej. `http://localhost/login`)
2. Ingresa tu **correo electrónico institucional**
3. Ingresa tu **contraseña**
4. Haz clic en **"Iniciar Sesión"**

> **Nota**: Si es tu primera vez o no recuerdas tu contraseña, contacta al administrador del sistema.

### URLs Importantes

| Función | Ruta |
|---------|------|
| Login | `/login` |
| Dashboard | `/dashboard` |
| Seguimiento público de tickets | `/service-management/track-ticket` |

---

## Roles del Sistema

El sistema cuenta con 7 niveles de acceso, cada uno con funciones específicas:

| Rol | Descripción | Acceso Principal |
|-----|-------------|------------------|
| **Solicitante** | Crea y da seguimiento a sus solicitudes | `/service-management/` |
| **Colaborador** | Resuelve tickets asignados, registra avances y puede cerrar | `/contributors` |
| **Operario** | Atiende tickets operativos y los marca como realizados | `/operario` |
| **Admin Área** | Audita resultados y completa tickets de su área | `/area-admin/dashboard` |
| **Administrador** | Gestiona tickets, usuarios, roles y configuración | `/dashboard` |
| **Monitor / Auditor** | Supervisa métricas y genera reportes (solo lectura) | `/monitor` |
| **Super Admin Técnico** | Configura parámetros técnicos del servidor | `/technical/storage-settings` |

---

## Guía por Rol

### Solicitante

**¿Qué puedes hacer?**

| Acción | Cómo hacerlo |
|--------|--------------|
| Crear una solicitud | Botón **"Nueva Solicitud"** en el dashboard |
| Ver tus tickets | Dashboard con estadísticas (Total, Pendientes, En Progreso, Completados) |
| Ver detalle de un ticket | Clic en **"Ver"** en la lista de solicitudes |
| Calificar un servicio | Cuando el ticket esté **Completado** o **Cerrado**, usa las estrellas (1-5) |
| Seguimiento sin login | Usa `/service-management/track-ticket` con tu número de ticket |

**Crear una solicitud:**

1. Haz clic en **"Nueva Solicitud"**
2. Completa el formulario:
    - **Título**: Sé descriptivo (ej. "Diseño de presentación para curso de Matemáticas")
   - **Tópico Principal**: Selecciona la categoría que define el departamento y SLA asignado
   - **Prioridad Inicial**: Define la urgencia (Baja, Media, Alta - Afecta operación, o Urgente - Suspende operación)
   - **Descripción**: Detalla qué necesitas, para qué y cuándo lo necesitas. Puedes pegar imágenes directamente en el campo de descripción.
3. Haz clic en **"Crear Ticket Formalmente"**
4. Recibirás un **número de ticket** para seguimiento

---

### Colaborador

**¿Qué puedes hacer?**

| Acción | Cómo hacerlo |
|--------|--------------|
| Ver tickets asignados | Dashboard con estadísticas personales |
| Ver detalle de un ticket | Clic en **"Ver"** en la lista |
| Registrar nota de avance | En el detalle del ticket, usa la tarjeta **"Agregar Nota de Avance"** |
| Cerrar un servicio | Botón **"Cerrar Servicio"** (solo si cumple las condiciones) |

**Registrar una nota de avance:**

1. Abre el ticket asignado
2. En **"Agregar Nota de Avance"**:
   - **Nota / Descripción**: Describe lo realizado. Puedes pegar imágenes como evidencia
3. Haz clic en **"Guardar Nota"**

> **Nota**: El porcentaje de progreso se calcula **automáticamente** según las tareas completadas en los Sprints del proyecto. No se ingresa manualmente.

**Cerrar un servicio (condiciones):**

- Todas las tareas de los Sprints completadas (progreso automático al **100%**)
- Fase ADDIE en **Evaluation**
- Incluir detalle de la solución y opcionalmente URL del recurso entregado

---

### Administrador

**¿Qué puedes hacer?**

| Módulo | Funciones |
|--------|-----------|
| **Tickets** | Ver, asignar colaboradores, establecer prioridad, cerrar, reabrir |
| **Usuarios** | Crear, editar, asignar roles y áreas |
| **Roles** | Gestionar roles del sistema |
| **Puestos de Trabajo** | Configurar posiciones para asignaciones |
| **Tópicos de Servicio** | Configurar tópicos, SLA, departamentos y gestores |
| **Áreas** | Estructura organizacional |
| **Facultades / Programas** | Clasificación académica |
| **Reportes** | Exportar tickets, colaboradores y progreso a Excel o CSV |

**Gestionar un ticket:**

1. Ve a `Admin > Tickets`
2. Haz clic en **"Ver"** en el ticket deseado
3. En **"Equipo de Trabajo"**, asigna o remueve colaboradores
4. Establece prioridad si es necesario
5. Puedes cerrar o reabrir el ticket según corresponda

**Reabrir un ticket:**

1. Accede al ticket cerrado
2. Selecciona **"Reabrir Ticket"**
3. El progreso se reinicia a 0% y la calificación se elimina

---

### Monitor / Auditor

**¿Qué puedes hacer?**

| Función | Descripción |
|---------|-------------|
| Dashboard analítico | Métricas generales, timeline de tickets, distribución por estado, actividad reciente, usuarios por rol y alertas de auditoría |
| Gráficos de calidad | Calificaciones del sistema (1-5 estrellas) |
| Exportación rápida | Botón **"Exportar Tickets"** en el panel para generar CSV de tickets visibles |
| Analytics | Visualizaciones de página, crecimiento de usuarios y tendencias de proyectos |
| Reportes | Actividad diaria, engagement de usuarios y rendimiento de proyectos |

> **Nota**: Este rol es **solo lectura**. No puedes modificar tickets, usuarios ni configuraciones.

---

### Super Admin Técnico

**¿Qué puedes hacer?**

| Función | Descripción |
|---------|-------------|
| Panel Técnico | Acceso a `/technical/storage-settings` |
| Configurar rutas de almacenamiento | Definir paths físicos para evidencias e imágenes |
| Gestionar App Settings | Modificar variables clave del sistema |

> **Precaución**: Modificar las rutas de almacenamiento sin verificar el servidor puede causar que las imágenes y evidencias existentes dejen de visualizarse. Prueba siempre en un entorno staging primero.

**Cuándo usar esta configuración:**

- Migración de servidor (cambio de rutas de directorio)
- Configuración de volúmenes de almacenamiento adicionales

---

## Funcionalidades Comunes

### Seguimiento Público de Tickets

Cualquier persona puede seguir el estado de un ticket sin iniciar sesión:

1. Accede a `/service-management/track-ticket`
2. Ingresa el **número de ticket** y la información de validación solicitada
3. Visualiza el progreso, fase ADDIE y estado actual

### Calificación de Servicios

- Disponible cuando el ticket está **Completado** o **Cerrado**
- Se puede calificar desde el dashboard o desde el seguimiento público
- Escala de **1 a 5 estrellas** con campo opcional de retroalimentación

### Modo Oscuro / Claro

- El sistema soporta ambos temas
- La preferencia se guarda en el navegador automáticamente
- Usa el botón de tema en la barra de navegación para cambiar

### Editor de Texto Enriquecido

Varios campos del sistema soportan formato rico:

- **Negritas**, *cursivas*, enlaces
- **Pegar imágenes** directamente desde el portapapeles (recortes de pantalla)
- Las imágenes se adjuntan automáticamente como evidencia

---

## Metodología ADDIE

Todos los tickets siguen 5 fases secuenciales que puedes ver en el historial de cada ticket:

| Fase | Nombre | Descripción |
|------|--------|-------------|
| 1 | **Analysis** (Análisis) | Se evalúa la solicitud y sus requerimientos |
| 2 | **Design** (Diseño) | Se planifica la solución |
| 3 | **Development** (Desarrollo) | Se construye el entregable |
| 4 | **Implementation** (Implementación) | Se aplica y ajusta la solución |
| 5 | **Evaluation** (Evaluación) | Se evalúa y cierra el ticket |

**Progreso y fases:**

| Porcentaje | Interpretación |
|------------|----------------|
| 0-25% | Fase inicial (Análisis/Diseño) |
| 26-50% | Diseño avanzado o desarrollo inicial |
| 51-75% | Desarrollo en progreso |
| 76-99% | Implementación y ajustes finales |
| 100% | Completado, listo para cierre |

---

## Preguntas Frecuentes

### ¿Cuánto tiempo tarda en procesarse mi solicitud?

Depende de la complejidad. Típicamente 1-2 días para la asignación y 1-4 semanas para el desarrollo completo.

### ¿Puedo modificar mi solicitud después de crearla?

No directamente. Deberás contactar al administrador para proveer nuevos alcances. En algunos casos, se puede reabrir el ticket.

### ¿Qué hago si el entregable no es lo que esperaba?

1. Califica honestamente con retroalimentación detallada
2. Solicita al administrador reabrir el ticket para ajustes

### ¿Puede un Colaborador cerrar un ticket?

Sí, si cumple las condiciones: progreso al 100% (tareas completadas) y fase en Evaluation.

### ¿Necesito un "Puesto de Trabajo" para asignar colaboradores?

No. El puesto es indicativo pero no obligatorio. Puedes asignar colaboradores sin definir un puesto específico.

### ¿Cómo exporto reportes?

Los administradores pueden generar reportes en formato Excel (.xls) o CSV desde el Módulo de Reportes. Los monitores pueden exportar tickets desde el panel de auditoría. Los archivos son compatibles con Excel, Power BI y Tableau.

### Funcionalidades operativas recientes

- El Operario marca tickets como Realizados; el Admin Área los audita antes de completarlos.
- Las incidencias generales pueden bloquear temporalmente un tópico.
- Los tickets relacionados pueden cerrarse desde un ticket principal y notificar a cada solicitante.
- El SLA cuenta jornadas laborales de lunes a viernes, festivos colombianos excluidos.
- El reporte de solicitantes frecuentes muestra quiénes generan más solicitudes según el alcance del usuario.

Consulta el detalle en [actualizacion_funcionalidades_2026.md](actualizacion_funcionalidades_2026.md).

---

## Soporte

Para asistencia técnica, preguntas o reportar errores:

- **Desarrollador Principal**: Daniel Agudelo
- **Repositorio**: https://github.com/DanielDev87
- **Issues**: https://github.com/DanielDev87/virtual-center-app/issues
- **Email de soporte institucional**: correo@institucion.edu.co

---

## Manuales Detallados

Para información más específica por rol, consulta los siguientes manuales en la carpeta `manuales/`:

| Manual | Contenido |
|--------|-----------|
| `manual_solicitante.md` | Guía completa para solicitantes |
| `manual_colaborador.md` | Guía completa para colaboradores |
| `manual_administrador.md` | Guía completa para administradores |
| `manual_monitor.md` | Guía completa para monitores/auditores |
| `manual_super_admin.md` | Guía completa para super admin técnico |
| `manual_tecnico.md` | Documentación técnica para desarrolladores |
| `manual_base_datos.md` | Diccionario de datos y estructura de BD |

---

**Mesa de Servicio Universidad Católica Luis Amigó**  
*Impulsando la Ciencia Abierta y la transformación digital en la educación.*

<!-- ACTUALIZACION_JUNIO_2026 -->
## Novedades Funcionales (Junio 2026)

- Carga masiva CSV reforzada con lectura UTF-8 y manejo explicito de comillas dobles como encapsulador de texto.
- Validacion estructural por fila en importaciones CSV para detectar columnas rotas por delimitador/comillas antes de escribir en BD.
- Mejora de importacion de cursos para relacion muchos-a-muchos con programas mediante tabla pivote course_program (manteniendo compatibilidad con program_id legado).
- Carga masiva de cursos con soporte de multiples referencias: program_id/program_ids, program_code/program_codes y program_name/program_names.
- Resolucion de ambiguedades de programas mejorada con filtros por faculty_id/faculty_name e institution_id/institution_name.
- Cuando program_code/program_name es duplicado y no se envia desambiguacion, la importacion puede vincular el curso a todos los programas coincidentes.
- Formularios de crear/editar cursos mejorados con selector multiple con busqueda (Tom Select), conservando compatibilidad del campo program_id.

