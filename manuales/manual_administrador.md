# Manual de Usuario - Administrador
## Sistema A-DDIE (Analysis, Design, Development, Implementation, Evaluation)

---

## Tabla de Contenidos

1. [Introducción](#introducción)
2. [Acceso al Sistema](#acceso-al-sistema)
3. [Dashboard Principal](#dashboard-principal)
4. [Gestión de Tickets](#gestión-de-tickets)
5. [Gestión de Usuarios](#gestión-de-usuarios)
6. [Gestión de Roles](#gestión-de-roles)
7. [Gestión de Puestos de Trabajo](#gestión-de-puestos-de-trabajo)
8. [Project Management (ADDIE + SCRUM)](#project-management)
9. [Módulo de Reportes](#módulo-de-reportes)
10. [Configuración del Sistema](#configuración-del-sistema)

---

## Introducción

### ¿Qué es A-DDIE?

A-DDIE es un sistema de gestión de servicios educativos que integra la metodología ADDIE (Analysis, Design, Development, Implementation, Evaluation) con prácticas SCRUM para la gestión efectiva de proyectos y tickets de soporte.

### Rol de Administrador

Como administrador, tienes acceso completo al sistema con las siguientes responsabilidades:

- Gestionar todos los tickets del sistema
- Administrar usuarios y sus roles
- Asignar colaboradores a tickets
- Monitorear el progreso de proyectos
- Generar reportes y análisis
- Configurar parámetros del sistema

---

## Acceso al Sistema

### Inicio de Sesión

1. Accede a la URL del sistema A-DDIE
2. Ingresa tu **correo electrónico** de administrador
3. Ingresa tu **contraseña**
4. Haz clic en **"Iniciar Sesión"**

> **Nota**: Si olvidaste tu contraseña, contacta al administrador del sistema.

### Navegación Principal

El panel de administración cuenta con:

- **Sidebar izquierdo**: Menú de navegación principal
- **Barra superior**: Información de usuario y opciones de perfil
- **Área central**: Contenido principal de cada sección

---

## Dashboard Principal

### Métricas Generales

El dashboard muestra 8 tarjetas de estadísticas clave:

#### Fila 1: Estadísticas de Tickets
- **Total Tickets**: Cantidad total de tickets en el sistema
- **Pendientes**: Tickets sin asignar o sin iniciar
- **En Progreso**: Tickets actualmente en desarrollo
- **Completados**: Tickets finalizados exitosamente

#### Fila 2: Métricas Avanzadas
- **Progreso Promedio**: Porcentaje promedio de avance de todos los tickets
- **Alta Prioridad**: Tickets urgentes que requieren atención inmediata
- **Usuarios Activos**: Cantidad de usuarios activos en el sistema
- **Roles Activos**: Cantidad de roles configurados

#### Fila 3: Métricas de Rendimiento
- **Calificación Promedio**: Promedio de calificaciones de los solicitantes (1-5 estrellas)
- **Tiempo Promedio**: Tiempo promedio de resolución en horas
- **Mejor Tiempo**: Tiempo del ticket resuelto más rápido

### Gráficos y Visualizaciones

#### Gráfico de Distribución de Calificaciones
- **Tipo**: Gráfico de torta (pie chart)
- **Muestra**: Distribución de calificaciones de 1 a 5 estrellas
- **Colores**: 
  - 1 estrella: Rojo
  - 2 estrellas: Naranja
  - 3 estrellas: Amarillo
  - 4 estrellas: Verde azulado
  - 5 estrellas: Verde

#### Tabla de Tickets Más Rápidos
Muestra los 5 tickets completados en menor tiempo con:
- Número de ticket (enlace directo)
- Nombre del solicitante
- Tiempo de resolución en horas

### Secciones Adicionales

- **Tickets Urgentes**: Lista de tickets de alta prioridad pendientes
- **Distribución por Fase ADDIE**: Cantidad de tickets en cada fase
- **Top Colaboradores**: Los 5 colaboradores con más tickets completados

---

## Gestión de Tickets

### Visualizar Lista de Tickets

**Ruta**: `Admin > Tickets`

La lista muestra **10 tickets por página** con:
- Número de ticket
- Título
- Solicitante
- Estado (Pendiente/En Progreso/Completado/Cancelado)
- Barra de progreso visual
- Fecha de creación
- Botón "Ver" para detalles

#### Filtros Disponibles
- Por estado (Pendiente, En Progreso, Completado, Cancelado)

### Ver Detalles de un Ticket

Haz clic en **"Ver"** en cualquier ticket para acceder a:

#### Información General
- Número y título del ticket
- Estado actual con badge de color
- Nivel de prioridad
- Tipo de solicitud
- Barra de progreso general
- Indicador si fue reabierto

#### Información del Solicitante
- Nombre completo
- Correo electrónico
- Descripción de la solicitud

#### Equipo de Trabajo Asignado
Tabla con todos los mediadores asignados mostrando:
- Nombre del colaborador
- Puesto de trabajo
- Fecha de asignación
- Botón para remover del equipo

#### Historial de Avances
- **Vista estándar**: Muestra todos los avances cronológicamente
- **Vista de ticket reabierto**: Separa avances en dos secciones:
  - Avances de Reapertura (desde la fecha de reapertura)
  - Avances Anteriores (antes de la reapertura)

### Asignar Mediadores al Ticket

#### Sistema Multi-Mediador

1. En la sección **"Equipo de Trabajo"**, ve a **"Agregar Miembro al Equipo"**
2. Selecciona el **mediador** del dropdown
3. Selecciona el **puesto de trabajo** que desempeñará
4. Haz clic en **"Asignar"**

> **Importante**: Puedes asignar múltiples mediadores al mismo ticket con diferentes puestos de trabajo.

#### Remover Mediador

1. En la tabla del equipo, haz clic en el botón **"X"** (rojo)
2. Confirma la acción
3. El mediador será marcado como "removido" pero se mantiene el historial

### Establecer Prioridad

**Niveles disponibles**:
- 🟢 **Baja**: Tickets que pueden esperar
- 🟡 **Media**: Prioridad normal
- 🟠 **Alta**: Requiere atención pronto
- 🔴 **Urgente**: Atención inmediata

**Pasos**:
1. En el detalle del ticket, ve a la tarjeta **"Establecer Prioridad"**
2. Selecciona el nivel deseado
3. Haz clic en **"Actualizar Prioridad"**

### Cerrar un Ticket

#### Requisitos para Cerrar como "Completado"

> **⚠️ VALIDACIONES ESTRICTAS**

Para marcar un ticket como completado, **DEBE cumplir**:

1. ✅ **Progreso al 100%**: La barra de progreso debe estar completa
2. ✅ **Fase ADDIE correcta**: Debe estar en fase "Implementation" o "Evaluation"
3. ✅ **Enlace al recurso**: Debes proporcionar la URL del recurso generado

#### Pasos para Cerrar

1. Ve a la tarjeta **"Cerrar Ticket"**
2. Selecciona **"Completado"** o **"Cancelado"**
3. Si seleccionas "Completado":
   - Ingresa el **enlace al recurso generado** (obligatorio)
   - El sistema validará progreso y fase ADDIE
4. Opcionalmente agrega notas administrativas
5. Haz clic en **"Cerrar Ticket"**

#### Mensajes de Error Comunes

- *"El progreso debe estar al 100%"*: El ticket no ha alcanzado el 100% de avance
- *"Debe completar todas las fases de ADDIE"*: El ticket no está en la fase correcta
- *"Debe proporcionar el enlace al recurso"*: Falta la URL del recurso

### Reabrir un Ticket

**Cuándo usar**: Cuando el solicitante requiere cambios adicionales después del cierre.

**Pasos**:
1. En un ticket cerrado, ve a la tarjeta **"Reabrir Ticket"**
2. Haz clic en **"Reabrir Ticket"**
3. Confirma la acción

**Efectos de la reapertura**:
- Estado cambia a "En Progreso"
- Progreso se reinicia a 0%
- Se marca como "Ticket Reabierto"
- Calificación anterior se borra (permite nueva evaluación)
- Se crea una sección separada para nuevos avances

### Calificar un Ticket (Evaluación ADDIE)

**Disponible**: Solo para tickets completados

#### Si ya fue calificado
Muestra:
- Estrellas visuales (1-5)
- Calificación numérica
- Retroalimentación del solicitante

#### Si no ha sido calificado
1. Ve a la tarjeta **"Evaluación ADDIE"**
2. Haz clic en las estrellas para seleccionar calificación (1-5)
3. Opcionalmente agrega retroalimentación
4. Haz clic en **"Guardar Evaluación"**

---

## Gestión de Usuarios

### Listar Usuarios

**Ruta**: `Admin > Usuarios`

Muestra tabla con:
- ID
- Nombre
- Correo electrónico
- Rol
- Estado (Activo/Inactivo)
- Acciones (Ver, Editar, Eliminar)

**Paginación**: 10 usuarios por página

### Crear Nuevo Usuario

1. Haz clic en **"Crear Usuario"**
2. Completa el formulario:
   - **Nombre completo**
   - **Correo electrónico** (único en el sistema)
   - **Contraseña** (mínimo 8 caracteres)
   - **Confirmar contraseña**
   - **Rol**: Selecciona uno (Admin, Monitor, Contributor, Requester)
   - **Puestos de trabajo**: Selecciona uno o varios (opcional)
   - **Estado**: Activo/Inactivo
3. Haz clic en **"Guardar"**

### Editar Usuario

1. En la lista, haz clic en **"Editar"**
2. Modifica los campos necesarios
3. Para cambiar contraseña:
   - Ingresa nueva contraseña
   - Confirma nueva contraseña
   - Si dejas vacío, la contraseña no cambia
4. Haz clic en **"Actualizar"**

### Eliminar Usuario

1. Haz clic en **"Eliminar"**
2. Confirma la acción
3. El usuario será eliminado permanentemente

> **⚠️ Precaución**: Esta acción no se puede deshacer.

---

## Gestión de Roles

### Roles del Sistema

El sistema A-DDIE maneja 4 roles principales:

1. **Admin**: Acceso completo al sistema
2. **Monitor**: Supervisión de tickets y colaboradores
3. **Contributor**: Colaborador que trabaja en tickets
4. **Requester**: Solicitante que crea tickets

### Crear Nuevo Rol

1. Ve a `Admin > Roles`
2. Haz clic en **"Crear Rol"**
3. Ingresa:
   - **Nombre del rol**
   - **Descripción** (opcional)
   - **Estado**: Activo/Inactivo
4. Haz clic en **"Guardar"**

### Editar/Eliminar Roles

Similar al proceso de usuarios.

---

## Gestión de Puestos de Trabajo

### ¿Qué son los Puestos de Trabajo?

Los puestos de trabajo definen las funciones específicas que un colaborador puede desempeñar en un ticket (ej: Diseñador Gráfico, Desarrollador, Analista).

### Crear Puesto de Trabajo

1. Ve a `Admin > Puestos de Trabajo`
2. Haz clic en **"Crear Puesto"**
3. Completa:
   - **Nombre del puesto**
   - **Descripción**
   - **Color** (para identificación visual)
   - **Estado**: Activo/Inactivo
4. Haz clic en **"Guardar"**

### Asignar Puestos a Usuarios

Los puestos se asignan al:
- Crear/editar un usuario
- Asignar un mediador a un ticket

---

## Project Management

### Acceso al Project Dashboard

**Desde**: Detalle de cualquier ticket

1. Haz clic en el botón **"Project Dashboard"** (icono de tablero)
2. Se abrirá el dashboard de gestión del proyecto

### Fases ADDIE

El sistema gestiona 5 fases del modelo ADDIE:

1. **Analysis** (Análisis): Investigación y definición de necesidades
2. **Design** (Diseño): Planificación y diseño de la solución
3. **Development** (Desarrollo): Creación del contenido/recurso
4. **Implementation** (Implementación): Puesta en marcha
5. **Evaluation** (Evaluación): Valoración y retroalimentación

#### Cambiar Fase ADDIE

1. En el Project Dashboard, ve a la sección **"Fases ADDIE"**
2. Haz clic en el botón de la fase deseada
3. La fase actual se actualizará

### Gestión de Sprints (SCRUM)

#### Crear Sprint

1. En la sección **"Sprints"**, haz clic en **"Crear Sprint"**
2. Completa:
   - **Nombre del sprint** (ej: "Sprint 1 - Análisis")
   - **Descripción**
   - **Fecha de inicio**
   - **Fecha de fin**
3. Haz clic en **"Guardar"**

#### Estados de Sprint

- **Planificado**: Sprint creado pero no iniciado
- **Activo**: Sprint en ejecución
- **Completado**: Sprint finalizado

#### Cambiar Estado de Sprint

1. En la lista de sprints, usa los botones de estado
2. Haz clic en el estado deseado
3. El sprint se actualizará

### Gestión de Tareas (Kanban)

#### Crear Tarea

1. En la sección **"Tareas"**, haz clic en **"Crear Tarea"**
2. Completa:
   - **Título de la tarea**
   - **Descripción**
   - **Sprint** (opcional)
   - **Asignado a** (colaborador)
   - **Estado inicial**: To Do
3. Haz clic en **"Guardar"**

#### Tablero Kanban

Las tareas se organizan en 3 columnas:

- **📋 To Do**: Tareas pendientes
- **🔄 In Progress**: Tareas en desarrollo
- **✅ Done**: Tareas completadas

#### Mover Tareas

1. Haz clic en los botones de estado en cada tarjeta de tarea
2. La tarea se moverá a la columna correspondiente

---

## Módulo de Reportes

**Ruta**: `Admin > Reportes` o botón en el Dashboard

### Tipos de Reportes

#### 1. Reporte de Tickets

**Exporta**: Lista completa de tickets con toda su información

**Filtros disponibles**:
- Rango de fechas (inicio - fin)
- Estado (Pendiente, En Progreso, Completado, Cancelado)
- Prioridad (Baja, Media, Alta, Urgente)
- Fase ADDIE

**Columnas del reporte**:
- Número de ticket
- Título
- Tipo de solicitud
- Estado
- Prioridad
- Fase ADDIE
- Progreso %
- Solicitante
- Mediador
- Facultad
- Programa
- Fecha de creación
- Última actualización

**Pasos**:
1. Selecciona los filtros deseados
2. Haz clic en **"Generar Reporte de Tickets"**
3. Se descargará un archivo CSV

#### 2. Reporte de Colaboradores

**Exporta**: Rendimiento de cada colaborador

**Columnas del reporte**:
- Nombre del colaborador
- Email
- Total de tickets asignados
- Tickets completados
- Tickets en progreso
- Tasa de completitud %

**Pasos**:
1. Haz clic en **"Generar Reporte de Colaboradores"**
2. Se descargará un archivo CSV

#### 3. Reporte de Progreso

**Exporta**: Historial de todos los avances registrados

**Filtros disponibles**:
- Rango de fechas

**Columnas del reporte**:
- Número de ticket
- Colaborador que registró el avance
- Descripción del avance
- Porcentaje de progreso
- Fecha del registro

**Pasos**:
1. Selecciona rango de fechas (opcional)
2. Haz clic en **"Generar Reporte de Progreso"**
3. Se descargará un archivo CSV

### Abrir Reportes en Excel

Los archivos CSV generados son compatibles con Excel:

1. Abre Microsoft Excel
2. Ve a `Archivo > Abrir`
3. Selecciona el archivo CSV descargado
4. Los datos se mostrarán en formato de tabla

---

## Configuración del Sistema

### Gestión de Tipos de Solicitud

**Ruta**: `Admin > Tipos de Solicitud`

Define los tipos de servicios que se pueden solicitar.

#### Crear Tipo de Solicitud

1. Haz clic en **"Crear Tipo"**
2. Completa:
   - **Nombre** (ej: "Diseño Gráfico", "Desarrollo Web")
   - **Descripción**
   - **Icono** (clase de Font Awesome)
   - **Color** (código hexadecimal)
   - **Estado**: Activo/Inactivo
3. Haz clic en **"Guardar"**

### Gestión de Facultades

**Ruta**: `Admin > Facultades`

Administra las facultades de la institución.

### Gestión de Programas

**Ruta**: `Admin > Programas`

Administra los programas académicos asociados a facultades.

### Gestión de Cursos

**Ruta**: `Admin > Cursos`

Administra los cursos asociados a programas.

---

## Casos de Uso Comunes

### Caso 1: Asignar y Gestionar un Nuevo Ticket

1. Un solicitante crea un ticket
2. Recibes notificación en el dashboard (aparece en "Pendientes")
3. Accedes al detalle del ticket
4. Estableces la prioridad según urgencia
5. Asignas colaboradores con sus puestos de trabajo
6. Los colaboradores registran avances
7. Monitoreas el progreso desde el dashboard
8. Cuando llega al 100% y está en fase correcta, cierras el ticket
9. Proporcionas el enlace al recurso generado
10. El solicitante califica el servicio

### Caso 2: Reabrir un Ticket por Solicitud de Cambios

1. Un ticket está marcado como "Completado"
2. El solicitante solicita modificaciones
3. Accedes al ticket cerrado
4. Haces clic en **"Reabrir Ticket"**
5. El progreso se reinicia a 0%
6. Los colaboradores registran nuevos avances en la sección de reapertura
7. Cuando se completa nuevamente, cierras el ticket
8. El solicitante puede calificar nuevamente

### Caso 3: Generar Reporte Mensual de Rendimiento

1. Accedes al módulo de reportes
2. Generas "Reporte de Colaboradores"
3. Generas "Reporte de Tickets" filtrando por el mes
4. Analizas las métricas en Excel
5. Identificas colaboradores destacados
6. Identificas áreas de mejora

---

## Preguntas Frecuentes

### ¿Puedo cambiar el rol de un usuario?

Sí, editando el usuario y seleccionando un nuevo rol.

### ¿Qué pasa si elimino un usuario que tiene tickets asignados?

Los tickets quedarán sin mediador asignado. Se recomienda reasignar antes de eliminar.

### ¿Puedo asignar más de un colaborador al mismo ticket?

Sí, el sistema soporta equipos de trabajo multi-mediador.

### ¿Cómo sé si un ticket fue reabierto?

Aparece un badge azul que dice "Ticket Reabierto" en el detalle del ticket.

### ¿Los reportes incluyen tickets eliminados?

No, solo tickets activos en el sistema.

### ¿Puedo cerrar un ticket sin que esté al 100%?

Solo si lo marcas como "Cancelado". Para "Completado" debe estar al 100%.

---

## Soporte Técnico

Para asistencia técnica o reportar problemas:

- **Email**: daniel_agudelo54232@elpoli.edu.co
- **Teléfono**: (+57) 3225917022
- **Horario**: Lunes a Viernes, 8:00 AM - 5:00 PM

---

**Versión del Manual**: 1.0  
**Última Actualización**: Diciembre 2025  
**Sistema**: A-DDIE v1.0
