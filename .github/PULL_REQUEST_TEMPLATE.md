# Pull Request

> Completa este formato antes de solicitar revision.
>
> CRITICO: no solicitar merge sin completar contexto, riesgo, pruebas, DoD y rollback.

## 1) Contexto y objetivo

OBLIGATORIO: este bloque debe describir con claridad el objetivo del cambio.

Describe brevemente el problema y que resuelve este PR.

- Ticket/Issue relacionado:
- Tipo de cambio: feat | fix | docs | refactor | hotfix
- Alcance del cambio:

## 2) Cambios principales

Lista concreta de lo implementado.

- 
- 
- 

<details>
<summary><strong>3) Riesgo tecnico (obligatorio)</strong></summary>

<br>

OBLIGATORIO: marca riesgos reales. Si no hay riesgos, selecciona "Ninguno de los anteriores".

Marca todo lo que aplique:

- [ ] Cambios de base de datos (migrations/seeders)
- [ ] Cambios de permisos, roles o middleware
- [ ] Cambios en autenticacion/sesion
- [ ] Cambios en rutas o contratos HTTP
- [ ] Cambios en almacenamiento/archivos
- [ ] Integracion con servicios externos
- [ ] Cambio de configuracion de entorno
- [ ] Ninguno de los anteriores

Impacto esperado en produccion:

- [ ] Bajo
- [ ] Medio
- [ ] Alto

</details>

<details open>
<summary><strong>4) Evidencia de pruebas (obligatorio)</strong></summary>

<br>

OBLIGATORIO: adjunta comandos y resultado. No dejar este bloque vacio.

Incluye comandos ejecutados y resultado.

### Pruebas ejecutadas

- [ ] Unit
- [ ] Feature
- [ ] Coverage (si aplica)
- [ ] Validacion manual del flujo

Comandos usados:

```bash
# Ejemplo
php artisan test
```

Resultado:

- Total tests:
- Total assertions:
- Observaciones:

</details>

<details open>
<summary><strong>5) Checklist Definition of Done (DoD)</strong></summary>

<br>

OBLIGATORIO: todos los checks aplicables deben quedar marcados antes de merge.

- [ ] El requerimiento funcional esta completo
- [ ] No rompe roles/permisos ni flujos existentes
- [ ] Pruebas nuevas o actualizadas incluidas cuando aplica
- [ ] No se exponen secretos, tokens o credenciales
- [ ] Documentacion actualizada (README/GUIAS) si hubo cambios de flujo
- [ ] El codigo mantiene coherencia con el modulo afectado

</details>

<details>
<summary><strong>6) Cambios de base de datos (si aplica)</strong></summary>

<br>

- [ ] Incluye migracion
- [ ] Migracion revisada
- [ ] Estrategia de rollback definida
- [ ] Seeders compatibles con entorno limpio

Detalles:

- Migraciones:
- Datos afectados:
- Riesgos:

</details>

<details open>
<summary><strong>7) Validacion funcional (obligatorio para cambios funcionales)</strong></summary>

<br>

OBLIGATORIO para cambios funcionales: describe al menos un flujo probado.

Describe el flujo probado manualmente:

1. 
2. 
3. 

Resultado:

- [ ] Exitoso
- [ ] Parcial
- [ ] Pendiente

</details>

<details>
<summary><strong>8) Plan de despliegue</strong></summary>

<br>

Pasos sugeridos para desplegar este cambio:

1. 
2. 
3. 

</details>

<details open>
<summary><strong>9) Plan de rollback (obligatorio)</strong></summary>

<br>

OBLIGATORIO: define pasos concretos para revertir el cambio.

Define como volver atras si falla en despliegue.

1. Revertir commit/tag a version estable.
2. Limpiar y reconstruir cache de aplicacion.
3. Restaurar datos si hubo migraciones destructivas.

Comandos utiles:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan view:cache
```

</details>

<details>
<summary><strong>10) Aprobaciones y ownership</strong></summary>

<br>

- Modulos tocados:
- Revisor primario sugerido:
- Revisor secundario sugerido:
- [ ] Requiere aprobacion dual (si toca modulos criticos)

</details>

<details>
<summary><strong>11) Evidencia adicional</strong></summary>

<br>

Adjunta capturas, payloads o logs relevantes.

- Capturas:
- Logs:
- Notas finales:

</details>

<!-- ACTUALIZACION_JUNIO_2026 -->
## Novedades Funcionales (Junio 2026)

- Carga masiva CSV reforzada con lectura UTF-8 y manejo explicito de comillas dobles como encapsulador de texto.
- Validacion estructural por fila en importaciones CSV para detectar columnas rotas por delimitador/comillas antes de escribir en BD.
- Mejora de importacion de cursos para relacion muchos-a-muchos con programas mediante tabla pivote course_program (manteniendo compatibilidad con program_id legado).
- Carga masiva de cursos con soporte de multiples referencias: program_id/program_ids, program_code/program_codes y program_name/program_names.
- Resolucion de ambiguedades de programas mejorada con filtros por faculty_id/faculty_name e institution_id/institution_name.
- Cuando program_code/program_name es duplicado y no se envia desambiguacion, la importacion puede vincular el curso a todos los programas coincidentes.
- Formularios de crear/editar cursos mejorados con selector multiple con busqueda (Tom Select), conservando compatibilidad del campo program_id.

