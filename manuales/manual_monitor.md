# Manual de Usuario - Monitor / Auditor
## Sistema Virtual Center

---

## Tabla de Contenidos

1. [Introducción](#introducción)
2. [Rol de Monitor o Auditor](#rol-de-monitor-o-auditor)
3. [Acceso al Sistema](#acceso-al-sistema)
4. [Dashboard Analítico](#dashboard-analítico)
5. [Módulo de Reportes](#módulo-de-reportes)
6. [Diferencias con otros Roles](#diferencias-con-otros-roles)
7. [Buenas Prácticas](#buenas-prácticas)

---

## Introducción

### ¿Qué es Virtual Center?

Virtual Center es el sistema de gestión de servicios educativos institucionales que procesa requerimientos multimedia, gráficos y de desarrollo. Emplea la metodología ADDIE (Analysis, Design, Development, Implementation, Evaluation) complementada con flujos de trabajo en SCRUM para el rastreo y organización ágil de proyectos.

### Rol de Monitor o Auditor

El rol de **Monitor** está diseñado específicamente para coordinadores académicos, decanos, gestores de calidad, líderes de proyecto o auditores internos cuya principal responsabilidad es la **observación exhaustiva del rendimiento y la revisión de métricas**, sin intervenir directamente en la matriz de configuración del software ni en la carga de código o diseño.

Tus responsabilidades implican:
- 👀 Supervisión continua de los KPI (Indicadores Clave de Rendimiento).
- 📊 Seguimiento de la carga de trabajo de todos los colaboradores asignados institucionalmente.
- 📉 Auditoría sobre tiempos de entrega (SLA) para Tickets según su prioridad.
- 📋 Generación de reportes tabulares y CSV para minería de datos y juntas directivas.

---

## Acceso al Sistema

### Inicio de Sesión

1. Accede a la URL del sistema Virtual Center preestablecida por la institución.
2. Ingresa tu **correo electrónico corporativo** de seguimiento ligado a la cuenta de Auditor.
3. Ingresa tu **contraseña**.
4. Haz clic en **"Iniciar Sesión"**.

> **Nota**: Puesto que los perfiles de Auditor no se pueden autoensamblar, debes solicitar tu acceso directamente al Administrador de la plataforma de la Facultad.

---

## Dashboard Analítico

Al iniciar sesión, te encontrarás con el **Dashboard del Monitor** (`/monitor`), una central de mando configurada como de solo-lectura pero altamente alimentada de datos visuales:

### Métricas Clave de Operación
- **Flujo de Pendientes**: Visualizarás qué tickets experimentan cuellos de botella desde su recepción hasta su clasificación organizacional.
- **Tiempos Promedios**: Análisis dinámicos del "Lead Time" frente a lo estipulado originalmente por el SLA del Gestor asignado. 
- **Distribución de Carga**: Tablas de cuáles Departamentos Académicos están exigiendo la mayoría de las solicitudes (ej., solicitudes de Creación de Contenido, Web, Video, Diseño Gráfico).

### Gráficos y Calidad
Verás gráficamente si los tickets completados han sido valorados positivamente por los solicitantes internos (con el sistema de valoración visualizado entre 1 a 5 estrellas). Ayudará contundentemente a la toma de decisiones para capacitaciones o refuerzos.

---

## Módulo de Reportes

El núcleo potente de tu control es la extracción de datos. Tienes el permiso estricto de visualizar la sección de **Reports** (`/monitor/reports` o  `/monitor/analytics`), exportando información que alimenta softwares externos.

#### Acciones Permitidas
1. **Reporte de Tickets (Exportar)**: Produce una lista filtrada por Rango de Fechas, Fase ADDIE del momento o prioridades. Útil para verificar si existen tickets urgentes ignorados o atascados en fase "Analysis".
2. **Reporte de Colaboradores**: Genera un archivo CSV del rendimiento de cada colaborador. Detalla a cabalidad las asignaciones de cada funcionario frente a cuántas ha completado para calcular su tasa interna.
3. **Reporte de Progreso**: Muestra históricamente los cambios reportados con comentarios o evidencias enriquecidas (Rich Text) guardadas en la base.

Los archivos originados en CSV son de fácil importación para Power BI, Tableau, Excel, o tu manipulador de base de datos preferido.

---

## Diferencias con otros Roles

La auditoría protege el ecosistema, garantizando que el usuario tenga transparencia visual máxima, y cero riesgo de manipulación de registros.

- No puedes asentar evidencias, añadir anexos o avanzar porcentualmente un ticket (limitativo a **Contributor/Colaborador**).
- No configuras variables de servidor institucionales (**Super Admin Técnico**).
- No creas cuentas para personal nuevo o configuras facultades lógicamente en base de datos (**Administrador Regular**).

Dispones estrictamente de privilegios de vigilancia general y reportaje maestro.

---

## Buenas Prácticas

- **Constancia Visual**: Mantén verificaciones semanales sobre las tarjetas de Alta Prioridad.
- **Generación Sistemática**: Recomendamos realizar las extracciones y vaciado CSV el último viernes o primer lunes natural de cada ciclo administrativo mensual en tu dependencia.
- **Integridad**: Emplea los datos del sistema basándote en la fecha y estampa actual para corroborar el seguimiento.

---

**Versión del Manual**: 1.0  
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

