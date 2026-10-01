# Manual de Usuario - Solicitante
## Sistema Virtual Center (Metodología A-DDIE)

---

## Tabla de Contenidos

1. [Introducción](#introducción)
2. [Acceso al Sistema](#acceso-al-sistema)
3. [Dashboard del Solicitante](#dashboard-del-solicitante)
4. [Crear una Nueva Solicitud](#crear-una-nueva-solicitud)
5. [Seguimiento de Solicitudes (Dashboard y Público)](#seguimiento-de-solicitudes-dashboard-y-público)
6. [Calificar un Servicio](#calificar-un-servicio)
7. [Entender el Proceso ADDIE](#entender-el-proceso-addie)
8. [Preguntas Frecuentes](#preguntas-frecuentes)

---

## Introducción

### ¿Qué es Virtual Center?

Virtual Center es un sistema de gestión de servicios educativos que te permite solicitar recursos, materiales y servicios para tus necesidades académicas. El sistema sigue la metodología ADDIE (Analysis, Design, Development, Implementation, Evaluation) para garantizar la calidad de los entregables.

### Rol de Solicitante

Como solicitante (Requester), puedes:

- 📝 Crear solicitudes de servicios educativos
- 👀 Ver el progreso de tus solicitudes en tiempo real
- 👥 Conocer quién está trabajando en tu solicitud
- ⭐ Calificar los servicios recibidos
- 📊 Consultar el historial de todas tus solicitudes

---

## Acceso al Sistema

### Inicio de Sesión

1. Accede a la URL del sistema Virtual Center
2. Ingresa tu **correo electrónico institucional**
3. Ingresa tu **contraseña**
4. Haz clic en **"Iniciar Sesión"**

> **Nota**: Si es tu primera vez, solicita tus credenciales al administrador del sistema.

### Interfaz Principal

Tu interfaz incluye:

- **Barra de navegación**: Logo de Virtual Center y menú de usuario
- **Botón "Nueva Solicitud"**: Acceso rápido para crear tickets
- **Dashboard**: Vista de todas tus solicitudes

---

## Dashboard del Solicitante

### Estadísticas de tus Solicitudes

En la parte superior verás 4 tarjetas con el resumen de tus tickets:

- **Total**: Todas las solicitudes que has creado
- **Pendientes**: Solicitudes que aún no han sido asignadas
- **En Progreso**: Solicitudes en las que se está trabajando
- **Completadas**: Solicitudes finalizadas

### Lista de Solicitudes

La tabla muestra **10 solicitudes por página** con:

#### Columnas
- **#**: Número único de tu solicitud
- **Título**: Nombre que le diste a tu solicitud
- **Tipo**: Categoría del servicio solicitado
- **Estado**: 
  - 🔵 **Pendiente**: Esperando asignación
  - 🟡 **En Progreso**: En desarrollo
  - 🟢 **Completado**: Finalizado
  - 🔴 **Cancelado**: No se completó
- **Fecha**: Cuándo creaste la solicitud
- **Acciones**: Botón "Ver" para detalles

---

## Crear una Nueva Solicitud

### Acceder al Formulario

**Opción 1**: Haz clic en el botón **"Nueva Solicitud"** (esquina superior derecha)  
**Opción 2**: En el dashboard, haz clic en **"Crear Nueva Solicitud"**

### Completar el Formulario

#### 1. Información Básica

**Título de la Solicitud**
- Sé descriptivo pero conciso
- Ejemplo: "Diseño de presentación para curso de Matemáticas"
- Evita: "Ayuda", "Necesito algo"

**Tipo de Solicitud**
Selecciona la categoría que mejor describa tu necesidad (Diseño Gráfico, Desarrollo Web, etc.).

#### 2. Información Académica

**Facultad** y **Programa Académico** se actualizarán según tu selección. Puedes definir el **Curso** si aplica de la misma forma.

#### 3. Descripción Detallada (Editor de Texto Enriquecido)

**Descripción de la Solicitud**
Este campo soporta texto enriquecido. Incluye características útiles como:
- Posibilidad de emplear negritas, cursivas o insertar enlaces.
- **Insertar Imágenes**: Puedes copiar y pegar imágenes directamente en la descripción. El sistema las procesará y adjuntará como parte de la evidencia.

✅ **Qué necesitas**: Describe claramente el recurso o servicio.
✅ **Para qué lo necesitas**: Explica el contexto y objetivo.
✅ **Características específicas**: Detalles técnicos, formato, dimensiones, etc.
✅ **Fecha límite**: Cuándo necesitas el entregable.

#### 4. Enviar la Solicitud

1. Revisa toda la información
2. Haz clic en **"Crear Solicitud"**
3. ¡Éxito! Verás un **Modal de Confirmación**
   - Este modal incluye el número de tu ticket recién generado y un resumen de lo solicitado. 
   - Puedes copiar o tomar nota de este número.
4. Tu solicitud aparecerá en el dashboard con estado "Pendiente" y un correo de notificación también te llegará.

---

## Seguimiento de Solicitudes (Dashboard y Público)

Tienes dos alternativas de seguimiento: desde el Dashboard y mediante el Sistema Público (sin requerir autorización).

### Ver Detalle de una Solicitud (Autenticado)

1. En tu dashboard, haz clic en **"Ver"** en la solicitud deseada
2. Se abrirá la vista detallada con la información general, barra de progreso con porcentajes, equipo de trabajo, y el historial de avances con las evidencias detallables e imágenes incrustadas.

### Seguimiento Público (No Autenticado)

Incluso sin iniciar sesión o para los usuarios a quienes se les ha derivado un ticket específico:
1. Accede a la URL de seguimiento de tickets del portal (ej. `.../service-management/track-ticket`).
2. Digita el número de ticket y alguna información de validación solicitada.
3. Se desplegará la vista al nivel del dashboard mostrando porcentajes de progreso y estatus completo de la solicitud en formato de lectura, para que siempre estés al tanto.

### Interpretar el Progreso

- **0-25%**: Fase inicial (Análisis/Diseño)
- **26-50%**: Diseño avanzado o desarrollo inicial
- **51-75%**: Desarrollo en progreso
- **76-99%**: Implementación y ajustes finales
- **100%**: Completado, listo para cierre

---

## Calificar un Servicio

### Cuándo Calificar

Podrás calificar cuando:
- ✅ El ticket esté marcado como **"Completado"**
- ✅ El ticket se haya cerrado.
- ✅ Aún no hayas calificado (o el ticket fue reabierto)

### Cómo Calificar (Dos Formas)

**1. Desde el Dashboard**
En el detalle de un ticket completado dentro de tu panel de usuario, ve a la sección **"Calificar Servicio"**.

**2. Desde la Interfaz Pública de Seguimiento**
Si estás usando el seguimiento `/track-ticket` (No Autenticado) y el ticket aparece cerrado, podrás calificar tu servicio sin tener que iniciar sesión ingresando las métricas en pantalla.

Para calificar:
1. Haz clic en las **estrellas** para seleccionar tu calificación (1 a 5 estrellas).
2. **Opcionalmente**, escribe retroalimentación en el campo de texto ("Qué te gustó", "Qué mejorar", etc.).
3. Haz clic en **"Enviar Calificación"**.

Tu calificación contribuye enormemente a la gestión de calidad que mantenemos en Virtual Center.

---

## Entender el Proceso ADDIE

ADDIE es una metodología de diseño instruccional que Virtual Center utiliza de base. Modela 5 fases secuenciales:

#### 1. 📋 Analysis (Análisis)
#### 2. 🎨 Design (Diseño)
#### 3. 🔨 Development (Desarrollo)
#### 4. 🚀 Implementation (Implementación)
#### 5. ⭐ Evaluation (Evaluación)

En el historial del ticket siempre verás en qué etapa del modelo nos encontramos.

---

## Preguntas Frecuentes

### ¿Cuánto tiempo tarda en procesarse mi solicitud?
Depende de la complejidad y del SLA del tópico. El tiempo se cuenta en jornada laboral de lunes a viernes de 7:00 a. m. a 5:00 p. m.; sábados, domingos y festivos colombianos no consumen el SLA. Si creas el ticket fuera de jornada, se recibirá para gestión el siguiente día hábil.

### ¿Puedo modificar mi solicitud después de crearla?
No directamente, deberás ponerte en contacto y proveer nuevos alcances, quizás acompañados de que deban reabrir el ticket más tarde.

### ¿Qué hago si el recurso entregado no es lo que esperaba?
1. Califica honestamente el servicio e incluye el texto con tu retroalimentación.
2. Solicita al administrador reabrir el ticket para ajustes.

### ¿Qué veo cuando mi solicitud se completa o cancela?

Cuando el ticket se completa verás la respuesta final del servicio y los recursos o evidencias autorizados. Si se cancela, verás el estado **Cancelado** y la justificación de cancelación. No se muestra el historial interno de trabajo ni las notas de auditoría.

### ¿Qué ocurre si existe una incidencia general?

Si un tópico está bloqueado por una incidencia general, el formulario mostrará un aviso con el motivo y no permitirá crear nuevas solicitudes para ese tópico hasta que el área responsable resuelva el problema.

---

## Soporte y Ayuda

Para asistencia o consultas:

- **Email**: correo@institucion.edu.co
- **Horario**: Lunes a Viernes, 8:00 AM - 5:00 PM

---

**Versión del Manual**: 1.1  
**Última Actualización**: Abril 2026  
**Sistema**: Virtual Center v1.1

---

## ¡Gracias por usar Virtual Center!

Esperamos que este sistema facilite la creación de recursos educativos de calidad para mejorar la experiencia de aprendizaje en nuestra institución.

<!-- ACTUALIZACION_JUNIO_2026 -->
## Novedades Funcionales (Junio 2026)

- Carga masiva CSV reforzada con lectura UTF-8 y manejo explicito de comillas dobles como encapsulador de texto.
- Validacion estructural por fila en importaciones CSV para detectar columnas rotas por delimitador/comillas antes de escribir en BD.
- Mejora de importacion de cursos para relacion muchos-a-muchos con programas mediante tabla pivote course_program (manteniendo compatibilidad con program_id legado).
- Carga masiva de cursos con soporte de multiples referencias: program_id/program_ids, program_code/program_codes y program_name/program_names.
- Resolucion de ambiguedades de programas mejorada con filtros por faculty_id/faculty_name e institution_id/institution_name.
- Cuando program_code/program_name es duplicado y no se envia desambiguacion, la importacion puede vincular el curso a todos los programas coincidentes.
- Formularios de crear/editar cursos mejorados con selector multiple con busqueda (Tom Select), conservando compatibilidad del campo program_id.

