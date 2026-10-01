# Actualización funcional 2026

**Sistema:** Mesa de Servicio Universidad Católica Luis Amigó
**Fecha:** Septiembre de 2026

## Roles y responsabilidades

### Operario

Rol operativo para infraestructura, mantenimiento y atención física:

- Consulta tickets asignados desde `/operario`.
- Actualiza tickets a `En Proceso`.
- Adjunta fotografías y evidencias de hasta 2 MB por archivo.
- Marca el trabajo como `Realizado para auditoría`, no como `Completado`.
- Devuelve tickets con motivo obligatorio.
- Si es Operario principal, puede marcar el ticket como realizado.
- Si es Operario secundario, solo consulta el equipo y las evidencias.
- Si hay un Contributor en el equipo, permanece en modo consulta.

### Contributor

- Puede tomar tickets disponibles del pool de sus tópicos.
- Un ticket tomado deja de estar disponible para toma simultánea.
- Otro Contributor puede solicitar unirse al equipo.
- El Contributor responsable aprueba o rechaza solicitudes de unión.
- Puede transferir tickets a tópicos de cualquier área, siempre que exista un colaborador válido en el tópico destino.
- Puede cerrar tickets según las reglas actuales de progreso y ADDIE.

### Admin Área

- Ve únicamente tickets de su área y alcance regional.
- Puede auditar tickets marcados como `Realizado por Operario`.
- Puede aprobarlos y convertirlos en `Completado`.
- Puede devolverlos a `En Proceso` con observación.
- Aprueba o rechaza solicitudes de asociación de tickets enviadas por Contributors.
- Puede activar incidencias generales por tópico.
- Recibe alertas por tickets devueltos por Operarios.

### Admin

- Gestiona tickets, usuarios, tópicos y equipos.
- Puede asociar tickets directamente.
- Puede cerrar grupos de tickets relacionados desde el ticket principal.
- Puede consultar el reporte de solicitantes frecuentes.

## Estados principales

| Estado | Significado |
|---|---|
| 1 | Pendiente |
| 2 | En Proceso |
| 3 | Completado |
| 4 | Cancelado |
| 5 | Realizado por Operario, pendiente de auditoría |

Un ticket completado o cancelado queda bloqueado para cambios hasta que sea reabierto.

## Equipos y pool de tickets

- El pool muestra disponibilidad del ticket.
- Un ticket disponible puede ser tomado por un Contributor.
- Al ser tomado, otros Contributors no pueden autoasignarlo.
- Para trabajo conjunto, se usa `Solicitar unirse`.
- La solicitud queda pendiente hasta aprobación del Contributor responsable.
- Admin y Admin Área pueden agregar miembros directamente, respetando el equipo configurado del tópico.
- Operario no puede asociar tickets ni aprobar asociaciones.

## Asociación de tickets

Los tickets que representan la misma incidencia pueden asociarse bajo un ticket principal.

- Admin y Admin Área pueden asociar directamente tickets abiertos.
- Contributor puede seleccionar uno o varios tickets y enviar una única solicitud agrupada.
- Admin Área aprueba o rechaza la solicitud agrupada.
- Al aprobar, los tickets hijos reciben `parent_ticket_id`.
- Al cerrar el ticket principal, los tickets asociados abiertos:
  - pasan a `Completado`;
  - reciben progreso del 100%;
  - reciben una entrada propia de historial;
  - reciben correo individual de cierre a sus solicitantes.
- No se mezclan evidencias ni historiales entre solicitantes.
- Tickets ya completados o cancelados no se sobrescriben.

## Transferencia entre tópicos

Un Contributor puede transferir un ticket a otro tópico activo aunque pertenezca a otra área, cuando el solicitante haya elegido mal el tópico.

La transferencia:

- Cambia el tópico y mediador principal.
- Exige que el colaborador seleccionado pertenezca al equipo destino.
- Registra el motivo en el historial.
- No cancela el ticket.
- Notifica el nuevo responsable según la configuración existente.

## Incidencias generales por tópico

Admin Área puede bloquear temporalmente un tópico por una incidencia general.

- Define título y mensaje visible.
- El solicitante ve un modal al seleccionar el tópico bloqueado.
- No puede crear solicitudes nuevas para ese tópico mientras la incidencia esté activa.
- El bloqueo también se valida en servidor.
- Los tickets existentes continúan atendidos.
- Al resolver la incidencia, el tópico vuelve a estar disponible.

## SLA y jornada laboral colombiana

El SLA cuenta únicamente:

- Lunes a viernes.
- De 7:00 a. m. a 5:00 p. m.
- Sin contar sábados, domingos ni festivos registrados.

Los festivos nacionales colombianos se almacenan en `holidays`. El sistema incluye festivos fijos, festivos trasladados por Ley Emiliani y fechas relacionadas con Semana Santa. También pueden registrarse días institucionales.

Un ticket creado fuera de jornada informa al solicitante que comenzará a gestionarse el siguiente día hábil.

## Notificaciones

- Cierre: correo al solicitante con la respuesta final.
- Cancelación: correo al solicitante con la justificación.
- Devolución por Operario: correo al asignador, alerta en dashboard y registro interno.
- SLA vencido: correo al solicitante, mediador principal y colaboradores activos. Se evita repetir el aviso con `response_overdue_notified_at`.

## Evidencias y respuesta final

Admin, Admin Área y Contributor pueden adjuntar hasta 5 archivos de máximo 2 MB al cerrar un servicio. También puede hacerlo Admin Área al aprobar un resultado de Operario.

El solicitante ve solamente:

- La respuesta final cuando el ticket está completado.
- El motivo oficial cuando el ticket está cancelado.
- Los recursos y evidencias autorizados.

No ve el historial interno completo, notas de auditoría ni motivos internos de devolución.

## Reportes

El reporte de solicitantes frecuentes muestra:

- Nombre, documento y correo.
- Total de tickets.
- Pendientes, en progreso, completados y cancelados.

El alcance depende del rol:

- Admin: todos los tickets.
- Admin Área: tickets de su área y alcance regional.
- Contributor: tickets donde participa como mediador principal o miembro activo.

Está disponible en formatos de visualización y exportación según el módulo.
