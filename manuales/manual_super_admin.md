# Manual de Usuario - Super Admin Técnico
## Sistema Virtual Center

---

## Tabla de Contenidos

1. [Introducción](#introducción)
2. [Rol de Super Admin Técnico](#rol-de-super-admin-técnico)
3. [Diferencias con un Administrador Regular](#diferencias-con-un-administrador-regular)
4. [Configuración Técnica del Almacenamiento](#configuración-técnica-del-almacenamiento)
5. [Buenas Prácticas e Impacto en Base de Datos](#buenas-prácticas-e-impacto-en-base-de-datos)
6. [Soporte](#soporte)

---

## Introducción

### ¿Qué es Virtual Center?

Virtual Center es el sistema de gestión de servicios educativos institucionales que incorpora un flujo rígido basado en la metodología ADDIE para el procesamiento y manejo de todos los proyectos orientados por la universidad.

### Rol de Super Admin Técnico

El **Super Admin Técnico** es el nivel transaccional y jerárquico más alto que posee el sistema. A diferencia de las utilidades de negocio de la mesa de ayuda (asignar perfiles, responder requerimientos o emitir Sprints en SCRUM), el rol de Super Admin Técnico se ha implementado de cara a la **configuración sistémica** que habitualmente gestiona el equipo central de Tecnologías de la Información (TI).

- ⚙️ Podrá calibrar parámetros que afectan el almacenamiento de archivos del servidor.
- ⚙️ Define las claves del diccionario `app_settings` y su comportamiento.
- ⚙️ Goza de todos los privilegios estándar de un Administrador regular pero está destinado a no intervenir en la operación diaria, sino la logística del hosting/almacenamiento y los enrutamientos base.

---

## Diferencias con un Administrador Regular

| Permiso / Capacidad | Administrador | Super Admin Técnico |
|---------------------|---------------|---------------------|
| Crear o reabrir tickets | Sí | Sí |
| Modificar Facultades o Programas | Sí | Sí |
| Asignar SLA y responsables de Tópicos | Sí | Sí |
| **Configuraciones Críticas de Archivos (Paths)** | No | **Sí** |
| Acceso al submódulo "Panel Técnico" | No | **Sí** |

---

## Configuración Técnica del Almacenamiento

**Ruta Protegida**: `Panel Lateral > Configuración Técnica` (ej.`/technical/storage-settings`)

Las actualizaciones recientes que habilitan el soporte al Editor de Texto Enriquecido (**Rich Text Editor**) y a la incrustación de copias e imágenes (pegar en el área de texto llamadas *Inline Evidences*), exigen en gran medida la declaración de rutas de memoria física en el servidor que guardan estos bytes.

### Modificar Rutas de Evidencia (Storage Paths)

1. Ingresa a la interfaz de **Configuraciones Técnicas**.
2. Hallarás formularios enfocados en los *Paths de Almacenamiento*.
3. Deberás estipular el path local o de infraestructura en el cual tu servidor alojará físicamente los anexos que se generen cada vez que un colaborador guarde imágenes como parte de su progreso.
4. Una vez definido y modificado: Todos los requerimientos nuevos mapearán estas directivas globalmente. 

> [!CAUTION]  
> Modificar las variables de Path sin aprovisionar y comprobar paralelamente el servidor, puede conllevar a incidentes masivos en donde los hipervínculos internos del texto enriquecido queden rotos y no se visualicen para los requerimientos en curso. Solicita pruebas en un entorno staging antes de hacerlo.

---

## Buenas Prácticas e Impacto en Base de Datos

Cada vez que aplicas una variable transversal operada desde tu panel técnico, Virtual Center inscribe diccionarios Key-Value en la tabla `app_settings` que enrutarán y re-establecerán el caché global instantáneamente.

### Cuándo Utilizar la Configuración:
1. **Migración de Servidor**: Si vas a migrar tu ambiente Virtual Center desde local Windows hacia un bloque de Amazon EC2 o Linux con estructuras de directorios distintas, aquí debes corregir la ruta para no perder la visualización de todos los adjuntos (ej: C:\laragon\www\... vs /var/www/...).
2. **Respaldo Secundario**: Cuando estipulas un volumen especial distinto para apartar carga estática porque tus recursos en la tabla `ticket_evidences` sobrepasan tus discos primarios.

---

## Soporte

La gestión del **Super Admin Técnico** debe recaer en responsables de la infraestructura tecnológica o ingenieros DevOps de la entidad por su alta sensibilidad. Para escalamientos adicionales a nivel código o depuración de Laravel en la carga de variables de entorno:

- **Soporte TI Institucional**: correo@institucion.edu.co

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

