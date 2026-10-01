# Guía Paso a Paso - Mesa de Servicio

## Universidad Católica Luis Amigó

**Versión**: 1.1  
**Última Actualización**: Mayo 2026  
**Sistema**: Mesa de Servicio v1.1 (Anteriormente Virtual Center)

---

## Tabla de Contenidos

1. [Introducción](#1-introducción)
2. [Funcionalidades Comunes](#2-funcionalidades-comunes)
3. [Metodología ADDIE](#3-metodología-addie)
4. [Solicitar un Servicio (Solicitante)](#4-solicitar-un-servicio-solicitante)
5. [Seguir un Ticket (sin login)](#5-seguir-un-ticket-sin-login)
6. [Trabajar un Ticket como Colaborador](#6-trabajar-un-ticket-como-colaborador)
7. [Trabajar Tickets como Operario](#7-trabajar-tickets-como-operario)
8. [Gestionar Tickets como Administrador](#8-gestionar-tickets-como-administrador)
9. [Generar Reportes como Monitor](#9-generar-reportes-como-monitor)
10. [Configuración Técnica como Super Admin](#10-configuración-técnica-como-super-admin)
11. [Editar Perfil de Usuario](#11-editar-perfil-de-usuario)
12. [Preguntas Frecuentes](#12-preguntas-frecuentes)
13. [Soporte](#13-soporte)
14. [Glosario Rápido](#14-glosario-rápido)

---

## 1. Introducción

### ¿Qué es la Mesa de Servicio?

La **Mesa de Servicio Universidad Católica Luis Amigó** es un sistema de gestión integral de tickets y proyectos educativos. Permite solicitar servicios, hacer seguimiento en tiempo real, colaborar en equipo y generar reportes de rendimiento, todo bajo la metodología **ADDIE** (Analysis, Design, Development, Implementation, Evaluation).

### Características Principales

- Creación y seguimiento de tickets de servicio
- Asignación multi-colaborador con roles flexibles
- Registro de avances con texto enriquecido e imágenes
- Sistema de calificación y retroalimentación
- Reportes exportables a CSV
- Seguimiento público de tickets (sin necesidad de login)
- Modo oscuro/claro
- Notificaciones por correo electrónico
- Directorio público de colaboradores

### Roles del Sistema

El sistema cuenta con 5 niveles de acceso, cada uno con funciones específicas:

| Rol                     | Descripción                                         | Acceso Principal              |
| ----------------------- | --------------------------------------------------- | ----------------------------- |
| **Solicitante**         | Crea y da seguimiento a sus solicitudes             | `/service-management/`        |
| **Colaborador**         | Resuelve tickets asignados, registra avances y puede cerrar | `/contributors`        |
| **Operario**            | Atiende tickets operativos, adjunta evidencias y marca realizados | `/operario`       |
| **Administrador de Área** | Audita tickets de su área y completa resultados operativos | `/area-admin/dashboard` |
| **Administrador**       | Gestiona tickets, usuarios, roles y configuración   | `/dashboard`                  |
| **Monitor / Auditor**   | Supervisa métricas y genera reportes (solo lectura) | `/monitor`                    |
| **Super Admin Técnico** | Configura parámetros técnicos del servidor          | `/technical/storage-settings` |

### URLs Importantes

| Función                        | Ruta                               |
| ------------------------------ | ---------------------------------- |
| Login                          | `/login`                           |
| Dashboard                      | `/dashboard`                       |
| Seguimiento público de tickets | `/service-management/track-ticket` |
| Directorio de colaboradores    | `/collaborators`                   |
| Edición de perfil              | `/profile/edit`                    |

---

## 2. Funcionalidades Comunes

### Seguimiento Público de Tickets

Cualquier persona puede seguir el estado de un ticket sin iniciar sesión:

1. Accede a `/service-management/track-ticket`
2. Ingresa el **número de ticket** y el **número de documento del solicitante**
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

- **Negritas**, _cursivas_, enlaces
- **Pegar imágenes** directamente desde el portapapeles (recortes de pantalla)
- Las imágenes se adjuntan automáticamente como evidencia

### Notificaciones por Correo

El sistema envía notificaciones automáticas:

- Al crear un ticket, el solicitante recibe confirmación por correo
- Al cerrar un ticket, se notifica al solicitante para su calificación
- Al calificar un servicio, se notifica al equipo de trabajo

### Directorio de Colaboradores

Accede a `/collaboradores` para ver el listado público de todos los colaboradores registrados en el sistema, con su información de contacto y roles.

---

## 3. Metodología ADDIE

Todos los tickets siguen 5 fases secuenciales que puedes ver en el historial de cada ticket:

| Fase | Nombre                              | Descripción                                 |
| ---- | ----------------------------------- | ------------------------------------------- |
| 1    | **Analysis** (Análisis)             | Se evalúa la solicitud y sus requerimientos |
| 2    | **Design** (Diseño)                 | Se planifica la solución                    |
| 3    | **Development** (Desarrollo)        | Se construye el entregable                  |
| 4    | **Implementation** (Implementación) | Se aplica y ajusta la solución              |
| 5    | **Evaluation** (Evaluación)         | Se evalúa y cierra el ticket                |

### Progreso y fases

| Porcentaje | Interpretación                       |
| ---------- | ------------------------------------ |
| 0-25%      | Fase inicial (Análisis/Diseño)       |
| 26-50%     | Diseño avanzado o desarrollo inicial |
| 51-75%     | Desarrollo en progreso               |
| 76-99%     | Implementación y ajustes finales     |
| 100%       | Completado, listo para cierre        |

---

## 4. Solicitar un Servicio (Solicitante)

**Rol**: Solicitante (Requester)

### Paso 1: Iniciar sesión (Opcional)

1. Accede a la URL del sistema (ej. `http://localhost/login`)
2. Ingresa tu correo electrónico institucional
3. Ingresa tu contraseña
4. Haz clic en **"Iniciar Sesión"**

> **Nota**: El inicio de sesión es opcional. Puedes crear un ticket como invitado sin iniciar sesión — el sistema te guiará a través de un asistente de 4 pasos donde primero validarás tu identidad con tu número de documento.

> **Nota**: Si es tu primera vez o no recuerdas tu contraseña, contacta al administrador del sistema.

### Paso 2: Acceder al formulario de solicitud

Desde tu dashboard, haz clic en el botón **"Nueva Solicitud"** ubicado en la cabecera de la página.

También puedes acceder directamente a: `/service-management/create`

### Paso 3: Completar el formulario

#### Información Básica

| Campo                 | Qué ingresar                                                                                                        |
| --------------------- | ------------------------------------------------------------------------------------------------------------------- |
| **Título**            | Descripción breve y clara (ej. "Diseño de presentación para curso de Matemáticas"). Evita: "Ayuda", "Necesito algo" |
| **Tópico Principal**  | Selecciona la categoría que mejor describe tu necesidad. Define el departamento y SLA asignado                      |
| **Prioridad Inicial** | Define la urgencia: Baja, Media, Alta (Afecta operación) o Urgente (Suspende operación)                            |

#### Evidencias

| Campo                  | Qué ingresar                                                                 |
| ---------------------- | ---------------------------------------------------------------------------- |
| **Enlace de Evidencia** | Opcional: URL de Google Drive u otro servicio de almacenamiento en la nube   |
| **Adjuntar Archivos**  | Hasta 5 archivos (máx 2MB cada uno). Formatos: pdf, jpg, jpeg, png, doc, docx, xls, xlsx, ppt, pptx, txt, zip, rar |

#### Descripción

En el campo de descripción (Rich Text Editor):

1. Escribe los detalles de lo que necesitas
2. Incluye:
   - ✅ **Qué necesitas**: Describe claramente el recurso o servicio
   - ✅ **Para qué lo necesitas**: Explica el contexto y objetivo
   - ✅ **Características específicas**: Detalles técnicos, formato, dimensiones, etc.
   - ✅ **Fecha límite**: Cuándo necesitas el entregable
3. **Para pegar una imagen**: haz un recorte de pantalla y pégalo con `Ctrl+V` directamente en el campo
4. El editor permite negritas, cursivas y enlaces

### Paso 4: Enviar la solicitud (Autenticado)

1. Revisa toda la información ingresada
2. Haz clic en **"Crear Ticket Formalmente"**
3. Aparecerá un modal de confirmación con tu **número de ticket**
4. **Anota o copia este número** — lo necesitarás para dar seguimiento
5. Recibirás un correo de notificación con la confirmación

### Paso 4.5: Crear Ticket como Invitado (sin login)

Si no deseas iniciar sesión:

1. Ve a `/service-management/create` sin iniciar sesión
2. Completa el formulario con la información básica, académica y evidencias
3. Completa la sección de validación de invitado:
   - **Número de Documento**: Tu documento de identidad (obligatorio)
   - **Nombre Completo**: Tu nombre (obligatorio)
   - **Correo Electrónico**: Tu correo institucional (obligatorio)
   - **Vínculo con la Institución**: Selecciona tu rol (Estudiante, Docente, Administrativo, Egresado o Persona Externa) (obligatorio)
   - **Aceptar Política**: Marca la casilla de aceptación de la política de tratamiento de datos (obligatorio)
4. Haz clic en **"Crear Ticket Formalmente"**
5. El sistema registrará tu información como usuario tipo Solicitante si no existes previamente
6. Recibirás tu número de ticket y se enviará confirmación a tu correo

### Paso 5: Ver tus solicitudes

Desde el dashboard (`/service-management/`) puedes ver:

- **Total**: todas tus solicitudes
- **Pendientes**: esperando asignación
- **En Progreso**: en las que se está trabajando
- **Completadas**: finalizadas

La tabla muestra las solicitudes paginadas con: número, título, estado, fecha de creación y botón "Ver".

#### Indicadores Visuales de Estado

- `badge bg-secondary` **Pendiente**: Esperando asignación
- `badge bg-warning` **En Progreso**: En desarrollo
- `badge bg-success` **Completado**: Finalizado
- `badge bg-danger` **Cancelado**: No se completó

### Paso 6: Ver detalle de una solicitud

1. En tu dashboard, haz clic en **"Ver"** en la solicitud deseada
2. Verás:
   - Información general del ticket (título, estado, prioridad, fechas, SLA)
   - Evidencias adjuntas (archivos y enlace de Google Drive)
   - Área responsable según el tópico asignado
   - Resultado del servicio (si está completado y tiene recurso)
   - Sección de calificación (si está completado)

### Paso 7: Editar o Eliminar Solicitud

Si el ticket está en estado **Pendiente**:

1. En el detalle del ticket, verás los botones **"Editar"** o **"Eliminar"**
2. **Editar**: Modifica los campos permitidos (título, descripción, evidencias) y guarda
3. **Eliminar**: Confirma la acción para borrar la solicitud (solo si no tiene avances registrados)

### Paso 8: Calificar un servicio

Cuando tu ticket esté **Completado** o **Cerrado**:

1. Ve al detalle del ticket o usa el seguimiento público
2. En la sección **"Calificar Servicio"**:
   - Haz clic en las estrellas (1 a 5)
   - Opcionalmente escribe retroalimentación ("Qué te gustó", "Qué mejorar", etc.)
3. Haz clic en **"Enviar Calificación"**

Tu calificación contribuye enormemente a la gestión de calidad del sistema.

---

## 5. Seguir un Ticket (sin login)

**Rol**: Público (cualquier persona con el número de ticket)

### Paso 1: Acceder al seguimiento público

1. Ve a la URL: `/service-management/track-ticket`
2. No necesitas iniciar sesión

### Paso 2: Buscar tu ticket

1. Ingresa el **número de ticket**
2. Ingresa el **número de documento del solicitante** (el mismo usado al crear el ticket)
3. Haz clic en **"Buscar"** o **"Consultar"**

### Paso 3: Ver el estado

Se mostrará:

- Estado actual del ticket (Pendiente, En Progreso, Completado, Cancelado)
- Porcentaje de progreso
- Fase ADDIE en la que se encuentra
- Historial de avances
- Equipo de trabajo

### Paso 4: Calificar (si aplica)

Si el ticket está cerrado y aún no has calificado:

1. En la vista de seguimiento público aparecerá la opción de calificación
2. Selecciona las estrellas (1-5) y opcionalmente escribes comentarios
3. Envía la calificación

---

## 6. Trabajar un Ticket como Colaborador

**Rol**: Colaborador (Contributor)

### Paso 1: Iniciar sesión

1. Accede a `/login`
2. Ingresa tus credenciales de colaborador

### Paso 2: Ver tu dashboard

Al iniciar sesión serás redirigido a `/contributors` donde verás:

- **Total**: tickets asignados
- **Pendientes**: sin iniciar
- **En Progreso**: activos
- **Completados**: finalizados
- **Promedio de Calificación**: Estrellas promedio recibidas en tickets completados
- **Distribución de Calificaciones**: Gráfico de barras con tickets calificados de 1 a 5 estrellas
- **Solicitudes Pendientes de Calificar**: Tickets completados que los solicitantes aún no califican

La tabla muestra: número, título, tipo/tópico, rol/puesto, solicitante, estado, fecha y botón "Ver".

#### Indicadores Visuales

- **Badge de estado**: Color según el estado
  - Gris: Pendiente
  - Amarillo: En Progreso
  - Verde: Completado
  - Rojo: Cancelado

### Paso 2.5: Cambiar Prioridad del Ticket

Como colaborador asignado, puedes ajustar la prioridad:

1. Abre el ticket asignado
2. En la sección de detalles, selecciona la nueva prioridad:
   - 1: Baja
   - 2: Media
   - 3: Alta
   - 4: Urgente
3. Haz clic en **"Actualizar Prioridad"**
4. El tiempo SLA se ajustará automáticamente según la prioridad

### Paso 3: Abrir un ticket asignado

1. Haz clic en **"Ver"** en el ticket que deseas trabajar
2. Se abrirá la vista detallada con:
   - Información del ticket (estado, prioridad, fase ADDIE)
   - Información del solicitante y descripción
   - Equipo de trabajo (tabla con todos los colaboradores asignados)
   - Historial de avances y evidencias
   - Archivos adjuntos y enlaces de evidencia

### Paso 4: Registrar una nota de avance

1. En la sección **"Agregar Nota de Avance"**:

| Campo                   | Descripción                                                                               |
| ----------------------- | ----------------------------------------------------------------------------------------- |
| **Nota / Descripción**  | Describe lo que avanzaste. Puedes usar negritas, cursivas y **pegar imágenes** con `Ctrl+V` |

> **Nota**: El porcentaje de progreso ahora se calcula **automáticamente** según las tareas completadas en los Sprints del proyecto. No es necesario ingresarlo manualmente.

2. Haz clic en **"Guardar Nota"**
3. El avance aparecerá en el historial con tu nombre y fecha

#### Ejemplos de avance ideal

- ✅ "Se completó la vista principal. Adjunto imagen renderizada del componente web"
- ✅ "Proceso en un 50% para refactorización. La base de datos aceptó las correcciones en el esquema de ADDIE." (Anexa foto de consola)

> **Recomendación**: Registra avances al completar tareas significativas o al menos una vez por semana.

### Paso 5: Trabajo en Equipo

Algunos tickets tienen varios colaboradores trabajando bajo diferentes roles que cubren fases complejas en ADDIE (ej. Diseñador para Fase 2, Programador para Fase 3).

Es indispensable que si otro mediador interactúa, el progreso que ponga como total sea acordado en conjunto al equipo.

### Paso 6: Transferir un Ticket a Otro Gestor

Si eres el mediador principal (responsable inicial) del ticket:

1. En el detalle del ticket, haz clic en **"Transferir Ticket"**
2. Selecciona un nuevo Tipo de Solicitud (tópico) que tenga un gestor asignado diferente
3. Agrega una nota de transferencia (opcional)
4. Confirma la transferencia
5. El ticket se asignará al nuevo gestor, se registrará en el historial y se notificará al nuevo responsable

### Paso 7: Tickets Reabiertos

Si el administrador reabre un ticket:

- Los registros previos del progreso se categorizan como "Avances Anteriores"
- El seguimiento iniciará de cero en la nueva fase solicitada

### Paso 8: Actualizar la fase ADDIE (si aplica)

Si el proyecto avanzó a una nueva fase:

1. En la sección de fase ADDIE del ticket
2. Selecciona la nueva fase (Analysis → Design → Development → Implementation → Evaluation)
3. Confirma el cambio

### Paso 9: Gestionar Sprints y Tareas (SCRUM)

Si el ticket tiene metodología SCRUM activada:

1. Ve al **"Ver Tablero de Proyecto"** del ticket (proyecto) desde el detalle del ticket
2. Para crear un Sprint:
   - Completa el nombre, descripción y fechas
   - Haz clic en **"Crear Sprint"**
3. Para crear una Tarea:
   - Completa título y descripción
   - Asigna un responsable
   - Opcionalmente asígnala a un Sprint
4. **Tareas del Backlog**: Son tareas no asignadas a ningún Sprint; puedes crearlas y asignarlas posteriormente
5. Para actualizar el estado de una tarea:
   - Selecciona: `todo` → `in_progress` → `review` → `done`

> **Nota**: Las tareas del Kanban y Sprints de SCRUM no interfieren con el porcentaje principal general si no se desea, pero son vitales para tickets prolongados manejados por equipos con más de 3 contributors.

### Paso 10: Cerrar un servicio

**Condiciones requeridas:**

- Todas las tareas de los Sprints completadas (progreso automático al **100%**)
- Fase ADDIE en **Evaluation**
- Incluir detalle de la solución y opcionalmente una URL del recurso entregado

Si se cumplen las condiciones:

1. En el detalle del ticket, en la sección "Progreso del Ticket", verás el botón **"Cerrar Servicio"**
2. Completa el detalle de la solución y opcionalmente la URL del recurso
3. Haz clic en **"Cerrar Servicio"**
4. El ticket pasa a estado "Completado" y se notifica al solicitante

### Buenas Prácticas

#### Gestión del Tiempo

- Actualiza tus porcentajes y añade evidencias progresivamente semana a semana en lugar del mero día de cierre.

#### Calidad y Transparencia

- ¡Utiliza la herramienta de inserción de imagen! Proveer avances gráficos con el Rich Text en lugar del texto plano simple reduce la constante necesidad de aclaratorias con el usuario.

---

## 7. Trabajar Tickets como Operario

**Rol**: Operario

Accede a `/operario` para consultar los tickets asignados a tu usuario. Esta vista está optimizada para trabajo operativo desde computador o móvil.

### Responsabilidades del Operario

- Consultar el ticket y el equipo de trabajo.
- Actualizar el ticket a **En Proceso**.
- Adjuntar evidencias, como fotografías o archivos de máximo 2 MB por archivo.
- Marcar el trabajo como **Realizado para auditoría**, con progreso del 100%.
- Devolver el ticket a la cola de no asignados indicando obligatoriamente el motivo.

Si hay varios Operarios, solo el Operario principal puede marcarlo como Realizado. Los demás pueden consultar el ticket y el equipo. Si el equipo incluye un Contributor, los Operarios quedan en modo consulta y el Contributor conserva el flujo de cierre.

El estado **Realizado** no es el cierre oficial. El Admin Área debe revisar las evidencias, corregir la descripción si es necesario y aprobar el resultado para cambiarlo a **Completado**. Al aprobarlo, el solicitante recibe la notificación de cierre.

### Devolución de tickets

Al devolver un ticket, el sistema solicita confirmación, registra el motivo, informa por correo al usuario que realizó la asignación y muestra una alerta en su dashboard. La alerta desaparece cuando el asignador la abre.

### Bloqueo de tickets finalizados

Los tickets **Completados** o **Cancelados** no pueden modificarse, asignarse ni recibir nuevos miembros hasta que sean reabiertos por un administrador.

---

## 8. Gestionar Tickets como Administrador

**Rol**: Administrador (Admin)

### Paso 1: Iniciar sesión

1. Accede a `/login`
2. Ingresa tus credenciales de administrador

### Paso 2: Ver el Dashboard
Al iniciar sesión serás redirigido a `/dashboard` donde verás:

- **Estadísticas Generales**: Total de tickets, listados pendientes, tickets en progreso y tickets finalizados
- **Métricas de Rendimiento**: Promedio de calificaciones, número de usuarios y roles activos
- **Gráficos**: Incluye distribuciones representadas en gráficas de torta sobre el cumplimiento de tiempos, así como el Chart global de calificaciones del sistema (1 a 5 estrellas)
- **Tablas de tickets con alertas**: Seguimientos urgentes que demanden la atención del Project Manager

El panel de administración cuenta con un **Sidebar izquierdo** fijo (u offcanvas en móvil) desde el que controlas todas tus opciones, una **Barra superior** para tu perfil y el **Área central** del módulo seleccionado.

### Paso 3: Gestionar Usuarios

**Ruta**: `Admin > Usuarios`

1. Para crear un usuario:
   - Haz clic en **"Crear Usuario"**
   - Completa: nombre, correo, contraseña, rol, área institucional
   - Opcionalmente: Link o documento adicional referencial
   - Haz clic en **"Guardar"**
2. Para editar un usuario:
   - Haz clic en **"Editar"** en el usuario deseado
   - Modifica los campos necesarios
   - Guarda los cambios

> **Nota**: Los usuarios también pueden editar su propio perfil en `/profile/edit`.

### Paso 4: Gestionar Roles

1. Ve a **Admin > Roles**
2. Crea o edita roles del sistema
3. Los roles disponibles son: Admin, Monitor, Contributor, Requester

### Paso 5: Gestionar Puestos de Trabajo

1. Ve a **Admin > Puestos de Trabajo**
2. Configura posiciones para asignaciones
3. Los puestos son indicativos pero no obligatorios
4. Puedes asignar colaboradores sin definir un puesto específico

### Paso 6: Gestionar Tickets

#### Ver lista de tickets

1. En el sidebar izquierdo, ve a **Admin > Tickets**
2. Verás los tickets paginados y filtrados por estado

#### Ver detalle de un ticket

1. Haz clic en **"Ver"** en el ticket deseado
2. Podrás ver toda la información, equipo de trabajo, avances y opciones de gestión

#### Asignar un colaborador a un ticket

1. En el detalle del ticket, ve a la sección **"Equipo de Trabajo"**
2. Selecciona un usuario (Contributor) del menú desplegable
3. Opcionalmente selecciona un Puesto de Trabajo
4. Haz clic en **"Asignar"**
5. El colaborador aparecerá en la lista del equipo

> **Nota**: El sistema soporta un entorno **Multi-Mediador**. Puedes asignar múltiples colaboradores a un ticket.

#### Remover un colaborador

1. En la lista del equipo de trabajo
2. Haz clic en **"Remover"** junto al colaborador
3. Confirma la acción

#### Establecer prioridad

1. En el detalle del ticket
2. Selecciona la prioridad: Baja, Media, Alta o Urgente
3. Confirma el cambio

#### Cerrar o Cancelar un ticket

1. En el detalle del ticket, en la sección **"Cerrar Ticket"**
2. Selecciona el estado final en el dropdown:
   - **Completado** (requiere progreso 100%, fase en Evaluation y detalle de solución)
   - **Cancelado** (requiere motivo de cancelación)
3. Completa el detalle de la solución o el motivo según corresponda
4. Opcionalmente agrega un enlace al recurso generado
5. Confirma el cierre; se notificará al solicitante

#### Reabrir un ticket

1. Accede al ticket cerrado o cancelado (desde la lista filtrando por "Completado" o "Cancelado")
2. Haz clic en **"Reabrir Ticket"**
3. Los progresos de 100% se moverán al histórico como "Avances Anteriores"
4. El tracking comenzará desde 0% y se elimina la calificación anterior

#### Evaluar un ticket ADDIE (como Admin)

1. En el detalle de un ticket completado
2. Ve a la sección **"Evaluación ADDIE"**
3. Selecciona las estrellas (1-5) y agrega retroalimentación (opcional)
4. Guarda la evaluación

### Paso 7: Gestionar Tipos de Solicitud (Tópicos)

**Ruta**: `Admin > Tópicos de Servicio`

1. Para crear un nuevo tipo:
   - Haz clic en **"Crear"**
   - Completa: nombre, departamento, área, SLA (días), gestor responsable, icono FontAwesome
   - Guarda
2. Los tipos de solicitud determinan la clasificación y reglas de vencimiento de los tickets

### Paso 8: Gestionar Estructura Académica

| Módulo        | Ruta                           | Función                            |
| ------------- | ------------------------------ | ---------------------------------- |
| Facultades    | `Admin > Academic > Faculties` | Crear/editar facultades            |
| Programas     | `Admin > Academic > Programs`  | Crear/editar programas académicos  |
| Cursos        | `Admin > Academic > Courses`   | Crear/editar cursos                |
| Áreas         | `Admin > Academic > Areas`     | Crear/editar áreas institucionales |

### Paso 9: Gestionar Proyectos (ADDIE + SCRUM)

1. Desde el detalle de un ticket, haz clic en **"Ver Tablero de Proyecto"**
2. O ve a **Admin > Projects > [ticket]/dashboard**
3. Podrás:
   - Cambiar manualmente la fase ADDIE (de Analysis a Evaluation)
   - Crear y gestionar Sprints
   - Crear y asignar Tareas tipo Kanban
   - Actualizar estados de tareas

### Paso 10: Ver Reportes

1. Ve a **Admin > Reportes**
2. Selecciona el tipo de reporte:
   - **Tickets**: lista filtrada de tickets
   - **Colaboradores**: rendimiento individual
   - **Progreso**: histórico de avances
3. Aplica filtros (fechas, estado, fase)
4. Selecciona el formato (Excel .xls por defecto o CSV) y haz clic en **"Exportar"**

---

## 9. Generar Reportes como Monitor

**Rol**: Monitor / Auditor

> **Nota**: Las cuentas de Monitor no se pueden autoensamblar, debes solicitar tu acceso directamente al Administrador de la plataforma.

### Paso 1: Iniciar sesión

1. Accede a `/login`
2. Ingresa tus credenciales de monitor

### Paso 2: Ver el Dashboard Analítico

Al iniciar sesión serás redirigido a `/monitor` donde verás:

- **Tickets Totales, Completados, en Atención y Usuarios Activos**: métricas generales del sistema
- **Tickets Vencidos SLA, Cancelados, Tópicos Registrados y Calificación Promedio**: indicadores de rendimiento
- **Timeline de Tickets**: gráfico de línea con tickets creados y completados en los últimos 7 días
- **Tickets por Estado**: gráfico de dona con la distribución de estados
- **Tickets Recientes**: tabla con los últimos tickets creados
- **Actividad Reciente del Proceso**: seguimiento de actividad de usuarios
- **Usuarios por Rol**: distribución de usuarios por tipo de rol
- **Alertas de Auditoría**: notificaciones sobre incidentes críticos del sistema

### Paso 3: Ver Analytics

1. En el sidebar, ve a **Monitor > Analytics** (`/monitor/analytics`)
2. Visualiza métricas avanzadas y gráficos detallados

### Paso 4: Generar Reportes

1. Ve a **Monitor > Reports** (`/monitor/reports`)

#### Reporte de Tickets (Exportación rápida)

En el panel de auditoria hay un botón **"Exportar Tickets"** que genera un archivo CSV con los tickets visibles en la tabla.

#### Actividad diaria

Visualiza el resumen de actividad diaria de tickets creados en el sistema.

#### Engagement de usuarios

Muestra la actividad de usuarios por día.

#### Rendimiento de proyectos

Distribución de proyectos por estado.

> **Nota**: Para reportes más detallados con filtros (fechas, estado, fase ADDIE, prioridad) y exportación en Excel o CSV, los administradores pueden acceder al **Módulo de Reportes** desde el panel administrativo.

### Paso 5: Usar los archivos exportados

Los archivos exportados son compatibles con:

- Microsoft Excel
- Google Sheets
- Power BI
- Tableau
- Cualquier herramienta de análisis de datos

### Diferencias con otros Roles

La auditoría protege el ecosistema, garantizando que el usuario tenga transparencia visual máxima, y cero riesgo de manipulación de registros.

- No puedes asentar evidencias, añadir anexos o avanzar porcentualmente un ticket (limitativo a **Contributor/Colaborador**)
- No configuras variables de servidor institucionales (**Super Admin Técnico**)
- No creas cuentas para personal nuevo o configuras facultades lógicamente en base de datos (**Administrador Regular**)

Dispones estrictamente de privilegios de vigilancia general y reportaje maestro.

### Buenas Prácticas

- **Constancia Visual**: Mantén verificaciones semanales sobre las tarjetas de Alta Prioridad
- **Generación Sistemática**: Recomendamos realizar las extracciones y vaciado CSV el último viernes o primer lunes natural de cada ciclo administrativo mensual
- **Integridad**: Emplea los datos del sistema basándote en la fecha y estampa actual para corroborar el seguimiento

---

## 10. Configuración Técnica como Super Admin

**Rol**: Super Admin Técnico

El **Super Admin Técnico** es el nivel transaccional y jerárquico más alto que posee el sistema. A diferencia de las utilidades de negocio de la mesa de ayuda, el rol de Super Admin Técnico se ha implementado de cara a la **configuración sistémica** que habitualmente gestiona el equipo central de Tecnologías de la Información (TI).

### Diferencias con un Administrador Regular

| Permiso / Capacidad                              | Administrador | Super Admin Técnico |
| ------------------------------------------------ | ------------- | ------------------- |
| Crear o reabrir tickets                          | Sí            | Sí                  |
| Modificar Facultades o Programas                 | Sí            | Sí                  |
| Asignar SLA y responsables de Tópicos            | Sí            | Sí                  |
| **Configuraciones Críticas de Archivos (Paths)** | No            | **Sí**              |
| **Configuración de Branding Institucional**       | No            | **Sí**              |
| Acceso al submódulo "Panel Técnico"              | No            | **Sí**              |

### Paso 1: Iniciar sesión

1. Accede a `/login`
2. Ingresa tus credenciales de Super Admin Técnico

### Paso 2: Configurar Almacenamiento

**Ruta Protegida**: `Panel Lateral > Configuración Técnica` (ej. `/technical/storage-settings`)

Las actualizaciones recientes que habilitan el soporte al Editor de Texto Enriquecido (**Rich Text Editor**) y a la incrustación de copias e imágenes, exigen en gran medida la declaración de rutas de memoria física en el servidor.

#### Modificar Rutas de Evidencia (Storage Paths)

1. Ingresa a la interfaz de **Configuraciones Técnicas**
2. Hallarás formularios enfocados en los _Paths de Almacenamiento_
3. Deberás estipular el path local o de infraestructura en el cual tu servidor alojará físicamente los anexos
4. Una vez definido y modificado: Todos los requerimientos nuevos mapearán estas directivas globalmente

> **⚠️ Precaución**: Modificar las variables de Path sin aprovisionar y comprobar paralelamente el servidor, puede conllevar a incidentes masivos en donde los hipervínculos internos del texto enriquecido queden rotos y no se visualicen para los requerimientos en curso. Solicita pruebas en un entorno staging antes de hacerlo.

### Paso 3: Configurar Branding Institucional

**Ruta**: `Panel Lateral > Configuración de Branding` (ej. `/technical/branding-settings`)

Permite personalizar la identidad visual de la Mesa de Servicio:

1. **Subir Logo/Activos**: Sube el logotipo de la institución y otros activos de imagen
2. **Editar Configuración**: Modifica colores, textos y elementos visuales
3. **Exportar Branding**: Exporta la configuración actual a un archivo JSON
4. **Importar Branding**: Carga una configuración previa desde un archivo JSON

> **Nota**: Esta funcionalidad es útil al migrar el sistema o cambiar la identidad visual institucional.

### Cuándo Utilizar la Configuración

1. **Migración de Servidor**: Si vas a migrar tu ambiente desde local hacia un bloque de Amazon EC2 o Linux con estructuras de directorios distintas (ej: `C:\laragon\www\...` vs `/var/www/...`)
2. **Respaldo Secundario**: Cuando estipulas un volumen especial distinto para apartar carga estática porque tus recursos en la tabla `ticket_evidences` sobrepasan tus discos primarios
3. **Cambio de Identidad Visual**: Al actualizar el logotipo o colores institucionales

### Impacto en Base de Datos

Cada vez que aplicas una variable transversal operada desde tu panel técnico, la Mesa de Servicio inscribe diccionarios Key-Value en la tabla `app_settings` que enrutarán y re-establecerán el caché global instantáneamente.

---

## 11. Editar Perfil de Usuario

**Todos los usuarios autenticados**

### Paso 1: Acceder a la edición de perfil

1. Haz clic en tu nombre/avatar en la barra superior
2. Selecciona **"Editar Perfil"** o ve directamente a `/profile/edit`

### Paso 2: Modificar información

1. Actualiza tus datos:
   - **Nombre**: Tu nombre completo
   - **Correo Electrónico**: Tu correo institucional
   - **Teléfono**: Tu número de contacto (opcional)
   - **Profesión**: Tu cargo o profesión (opcional, ej. Diseñador Gráfico)
   - **Biografía / Frase**: Una breve descripción sobre ti (opcional, máx. 500 caracteres)
   - **Contraseña**: Opcional, requiere ingresar tu contraseña actual
2. **Avatar**: Sube una imagen de perfil (formatos: jpg, png, gif, máx. 2MB) o elimina el actual
3. Haz clic en **"Guardar Cambios"**

> **Nota**: Los administradores pueden editar cualquier usuario desde **Admin > Usuarios**, pero cada usuario puede editar su propio perfil aquí.

---

## 12. Preguntas Frecuentes

### ¿Cuánto tiempo tarda en procesarse mi solicitud?

Depende de la complejidad. Típicamente 1-2 días para la asignación y 1-4 semanas para el desarrollo completo.

### ¿Puedo modificar mi solicitud después de crearla?

Sí, si el ticket está en estado Pendiente, puedes editarlo desde el detalle del ticket. Si ya está en progreso, contacta al administrador.

### ¿Qué hago si el entregable no es lo que esperaba?

1. Califica honestamente con retroalimentación detallada
2. Solicita al administrador reabrir el ticket para ajustes

### ¿Puede un Colaborador cerrar un ticket?

Sí, si cumple las condiciones: progreso al 100% (tareas completadas) y fase en Evaluation.

### ¿Necesito un "Puesto de Trabajo" para asignar colaboradores?

No. El puesto es indicativo pero no obligatorio. Puedes asignar colaboradores sin definir un puesto específico.

### ¿Cómo exporto reportes?

Los administradores pueden generar reportes en formato Excel (.xls) o CSV desde el Módulo de Reportes en el panel administrativo. Los monitores pueden exportar tickets desde el panel de auditoria. Los archivos son compatibles con Excel, Power BI y Tableau.

### ¿Puede un Contribuidor cerrar un ticket?

Sí, en la actualización moderna de la Mesa de Servicio, los Colaboradores (Contributors) pueden auto-cerrar sus propios proyectos una vez han adjuntado todo lo necesario y el sistema valida las condiciones lógicas de metodología ADDIE y porcentaje de avance.

### ¿Puedo crear un ticket sin iniciar sesión?

Sí, puedes crear un ticket como invitado proporcionando tu número de documento, nombre, correo, vínculo con la institución y aceptando la política de tratamiento de datos.

### ¿Cómo transfiero un ticket a otro gestor?

Solo el mediador principal puede transferir el ticket a otro gestor cambiando el Tipo de Solicitud (tópico) a uno que tenga un gestor diferente asignado.

---

## 13. Soporte

Para asistencia técnica, preguntas o reportar errores:

- **Desarrollador Principal**: Daniel Agudelo
- **Repositorio**: <https://github.com/DanielDev87>
- **Issues**: <https://github.com/DanielDev87/virtual-center-app/issues>
- **Email de soporte institucional**: <correo@institucion.edu.co>
- **Horario de atención**: Lunes a Viernes, 8:00 AM - 5:00 PM

### Soporte Técnico (Super Admin)

La gestión del **Super Admin Técnico** debe recaer en responsables de la infraestructura tecnológica o ingenieros DevOps de la entidad por su alta sensibilidad.

- **Soporte TI Institucional**: <correo@institucion.edu.co>

---

## 14. Glosario Rápido

| Término              | Significado                                                                       |
| -------------------- | --------------------------------------------------------------------------------- |
| **Ticket**           | Una solicitud de servicio o proyecto                                              |
| **ADDIE**            | Metodología de 5 fases: Analysis, Design, Development, Implementation, Evaluation |
| **SLA**              | Acuerdo de nivel de servicio (tiempo límite en días)                              |
| **Contributor**      | Colaborador asignado a resolver un ticket                                         |
| **Requester**        | Persona que crea la solicitud                                                     |
| **Gestor**           | Responsable de un tipo de solicitud                                               |
| **Sprint**           | Iteración de trabajo bajo metodología SCRUM                                       |
| **Rich Text Editor** | Editor de texto que permite formato e imágenes                                    |
| **App Settings**     | Diccionario Key-Value en base de datos para configuración del sistema             |
| **Storage Paths**    | Rutas físicas en el servidor para almacenamiento de evidencias e imágenes         |
| **Multi-Mediador**   | Sistema que permite asignar múltiples colaboradores a un ticket                   |
| **Kanban**           | Sistema visual de gestión de tareas (todo, in_progress, review, done)             |
| **Backlog**          | Tareas no asignadas a ningún Sprint                                               |
| **Branding**         | Identidad visual (logo, colores) de la institución                                |

---

## Manuales Detallados

Para información más específica por rol, consulta los siguientes manuales en la carpeta `manuales/`:

| Manual                    | Contenido                                  |
| ------------------------- | ------------------------------------------ |
| `manual_solicitante.md`   | Guía completa para solicitantes            |
| `manual_colaborador.md`   | Guía completa para colaboradores           |
| `manual_administrador.md` | Guía completa para administradores         |
| `manual_monitor.md`       | Guía completa para monitores/auditores     |
| `manual_super_admin.md`   | Guía completa para super admin técnico     |
| `manual_tecnico.md`       | Documentación técnica para desarrolladores |
| `manual_base_datos.md`    | Diccionario de datos y estructura de BD    |

---

**Mesa de Servicio Universidad Católica Luis Amigó**  
_Impulsando la Ciencia Abierta y la transformacion digital en la educacion._

<!-- ACTUALIZACION_JUNIO_2026 -->
## Novedades Funcionales (Junio 2026)

- Carga masiva CSV reforzada con lectura UTF-8 y manejo explicito de comillas dobles como encapsulador de texto.
- Validacion estructural por fila en importaciones CSV para detectar columnas rotas por delimitador/comillas antes de escribir en BD.
- Mejora de importacion de cursos para relacion muchos-a-muchos con programas mediante tabla pivote course_program (manteniendo compatibilidad con program_id legado).
- Carga masiva de cursos con soporte de multiples referencias: program_id/program_ids, program_code/program_codes y program_name/program_names.
- Resolucion de ambiguedades de programas mejorada con filtros por faculty_id/faculty_name e institution_id/institution_name.
- Cuando program_code/program_name es duplicado y no se envia desambiguacion, la importacion puede vincular el curso a todos los programas coincidentes.

<!-- ACTUALIZACION_SEPTIEMBRE_2026 -->
## Novedades Funcionales (Septiembre 2026)

Consulta [actualizacion_funcionalidades_2026.md](actualizacion_funcionalidades_2026.md) para el detalle de Operario, auditoría, asociaciones de tickets, incidencias por tópico, SLA laboral colombiano, reportes y notificaciones.
- Formularios de crear/editar cursos mejorados con selector multiple con busqueda (Tom Select), conservando compatibilidad del campo program_id.

