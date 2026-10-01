# Reglas de Contribución al Proyecto 

## Ramas
- feature/* : nuevas funcionalidades
- release/* : preparación de versiones
- hotfix/* : arreglos urgentes

## Commits
- feat: nueva funcionalidad
- fix: corrección de bug
- docs: documentación
- refactor: mejoras de código

## Pull Requests
- Mínimo 1 revisor
- No aprobar tu propio PR
- Tamaño máximo: 400 líneas

## Revisión de Código
- Revisar en máximo 24 horas
- Comentarios constructivos
- Preguntar si no se entiende algo

<!-- ACTUALIZACION_JUNIO_2026 -->
## Novedades Funcionales (Junio 2026)

- Carga masiva CSV reforzada con lectura UTF-8 y manejo explicito de comillas dobles como encapsulador de texto.
- Validacion estructural por fila en importaciones CSV para detectar columnas rotas por delimitador/comillas antes de escribir en BD.
- Mejora de importacion de cursos para relacion muchos-a-muchos con programas mediante tabla pivote course_program (manteniendo compatibilidad con program_id legado).
- Carga masiva de cursos con soporte de multiples referencias: program_id/program_ids, program_code/program_codes y program_name/program_names.
- Resolucion de ambiguedades de programas mejorada con filtros por faculty_id/faculty_name e institution_id/institution_name.
- Cuando program_code/program_name es duplicado y no se envia desambiguacion, la importacion puede vincular el curso a todos los programas coincidentes.
- Formularios de crear/editar cursos mejorados con selector multiple con busqueda (Tom Select), conservando compatibilidad del campo program_id.

