# Guia para Programadores - app-services-ula

Esta guia esta enfocada en el trabajo de desarrollo diario sobre este proyecto Laravel 10.

## 1. Objetivo

- Onboarding tecnico rapido
- Flujo de trabajo consistente
- Menos errores en cambios de codigo, DB, rutas y permisos
- Criterios minimos para entregar PRs con calidad

## 2. Stack real del proyecto

- Backend: Laravel 10
- PHP: 8.2+
- Frontend: Laravel Mix + Bootstrap + JS
- DB: MySQL/MariaDB
- Testing: PHPUnit 10 (Unit + Feature)

Referencias:
- [composer.json](composer.json)
- [package.json](package.json)
- [phpunit.xml](phpunit.xml)

## 3. Estructura base que debes conocer

- [app/Http/Controllers](app/Http/Controllers): logica HTTP
- [app/Models](app/Models): modelos Eloquent
- [app/Services](app/Services): servicios de almacenamiento y utilidades de negocio
- [app/Http/Middleware](app/Http/Middleware): middleware custom (rol/permisos)
- [routes/web.php](routes/web.php): rutas web
- [resources/views](resources/views): vistas Blade
- [database/migrations](database/migrations): cambios de esquema
- [database/seeders](database/seeders): carga inicial de datos
- [tests/Unit](tests/Unit): pruebas unitarias
- [tests/Feature](tests/Feature): pruebas funcionales/integracion de flujos

## 4. Setup local para programar

Sigue primero [GUIA_EJECUCION_LOCAL.md](GUIA_EJECUCION_LOCAL.md).

Comandos base para arrancar:

```powershell
Set-Location "C:\Users\jean.montoyaca\Daniel\proyectosDaniel\app-services-ula"
composer install
npm install
Copy-Item .\env.example .\.env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=UserRoleSeeder
php artisan db:seed --class=CreatePepitaAdmin
npm run dev
php artisan serve
```

## 5. Reglas de negocio tecnicas clave

### 5.1 Roles y permisos

El control de acceso principal usa middleware `role:*` y la implementacion custom en [app/Http/Middleware/CheckRole.php](app/Http/Middleware/CheckRole.php).

Comportamientos importantes:
- Usuario no autenticado: redireccion a login
- `Monitor`: modo solo lectura (GET), excepto dashboard admin
- `Admin`, `Contributor`, `Requester`, `Super Admin Tecnico`: acceso por lista de roles en ruta

### 5.2 Rutas con closures

En [routes/web.php](routes/web.php) existen rutas con closure (por ejemplo tema AJAX y fallback).
Por esto no debes asumir `php artisan route:cache` como paso obligatorio en este proyecto.

### 5.3 Seeders legacy

El seeder principal llama migracion de usuarios legacy.
Para entornos limpios de desarrollo usa seeders puntuales:

```powershell
php artisan db:seed --class=UserRoleSeeder
php artisan db:seed --class=CreatePepitaAdmin
```

## 6. Flujo de trabajo recomendado (git)

Basado en [CONTRIBUTING.md](CONTRIBUTING.md):

1. Crear rama:
- `feature/*` para funcionalidad
- `hotfix/*` para correccion urgente
- `release/*` para preparacion de version

2. Commits con prefijo:
- `feat:` nueva funcionalidad
- `fix:` bug
- `docs:` documentacion
- `refactor:` mejora interna

3. PR:
- Al menos 1 revisor
- No auto aprobar
- Intentar mantener cambios pequenos y legibles

## 7. Como implementar un cambio sin romper el sistema

1. Identifica capa afectada:
- Ruta
- Controller
- Service
- Model/migracion
- Vista

2. Cambia primero la logica central (Service/Controller) y luego UI.

3. Agrega o ajusta pruebas:
- Unit para logica aislada
- Feature para endpoints/roles/flujo completo

4. Ejecuta pruebas del area cambiada primero, luego suite completa:

```powershell
php artisan test tests/Unit/...
php artisan test tests/Feature/...
php artisan test
```

### 7.1 Ejemplo practico (orden recomendado)

Caso: agregar una nueva funcionalidad de "filtro por programa" en el listado de cursos del panel admin.

1. Analisis rapido del impacto
- Revisar ruta, controlador, modelo y vista involucrados.
- Ubicar archivos base: [routes/web.php](routes/web.php), [app/Http/Controllers/Admin/AdminCourseController.php](app/Http/Controllers/Admin/AdminCourseController.php), [app/Models/Course.php](app/Models/Course.php), [resources/views/admin/academic/courses/index.blade.php](resources/views/admin/academic/courses/index.blade.php).

2. Crear rama de trabajo
- Ejemplo: `feature/filtro-programa-cursos`.

3. Implementar primero backend (logica)
- Actualizar controlador para leer `program_id` o `program_ids` desde request.
- Aplicar filtro Eloquent sin romper compatibilidad con datos legacy.

4. Ajustar modelo si hace falta
- Confirmar relaciones necesarias (`program()` y/o `programs()`).
- Si hay cambio de esquema, crear migracion reversible.

5. Implementar UI despues
- Agregar el selector en la vista index.
- Conservar valores seleccionados en recarga para no romper UX.

6. Probar en orden
- Probar manualmente flujo feliz: listar -> filtrar -> limpiar filtro.
- Probar casos limite: programa inexistente, curso sin programa, combinaciones vacias.
- Ejecutar pruebas:

```powershell
php artisan test tests/Feature
php artisan test
```

7. Verificacion tecnica final
- Revisar logs: `storage/logs/laravel.log`.
- Validar que no se afecten permisos por rol.

8. Documentar y preparar PR
- Actualizar guia o README si cambia el flujo.
- Abrir PR con: objetivo, archivos tocados, riesgos, pruebas ejecutadas y rollback.

Resultado esperado: cambio funcional, trazable y seguro, con impacto controlado en DB, permisos y UI.

## 8. Testing y cobertura

### 8.1 Tipos de prueba

- Unit: metodos concretos, mocks, ramas de error
- Feature: autenticacion, middleware, rutas, respuestas HTTP, DB

### 8.2 Convenciones usadas en este repo

- Uso frecuente de `DatabaseTransactions` para aislar pruebas de DB
- Uso de `Mail::fake()` para verificar notificaciones
- Verificacion explicita de roles por ruta

### 8.3 Cobertura

Para cobertura con Xdebug:

```powershell
$env:XDEBUG_MODE="coverage"
vendor/bin/phpunit --coverage-text --colors=never
```

Si la terminal corta salida, guardar a archivo:

```powershell
$env:XDEBUG_MODE="coverage" ; vendor/bin/phpunit --coverage-text --colors=never 2>&1 | Out-File "coverage_report.txt" -Encoding utf8
```

## 9. Criterios de calidad antes de abrir PR

Checklist minimo:
- El cambio cumple el objetivo funcional
- No rompe roles/permisos
- Pruebas nuevas pasan
- Suite general pasa o se explica claramente lo que falla
- No se suben secretos ni credenciales
- Documentacion actualizada si el flujo cambia

## 10. Troubleshooting frecuente

### 10.1 Error de version de PHP

Verifica:

```powershell
php -v
```

Debe ser 8.2+.

### 10.2 No conecta a DB

- Revisar `.env`
- Confirmar servicio MySQL/MariaDB levantado
- Confirmar base creada

### 10.3 Seed falla en entorno limpio

No ejecutes seed global primero. Usa:
- `UserRoleSeeder`
- `CreatePepitaAdmin`

### 10.4 Assets no se reflejan

- `npm run dev` o `npm run watch`
- Hard refresh navegador

## 11. Guia de lectura del dominio

Para entender funcionalmente el sistema antes de tocar codigo:
- [manuales/manual_administrador.md](manuales/manual_administrador.md)
- [manuales/manual_colaborador.md](manuales/manual_colaborador.md)
- [manuales/manual_monitor.md](manuales/manual_monitor.md)
- [manuales/manual_solicitante.md](manuales/manual_solicitante.md)
- [manuales/manual_super_admin.md](manuales/manual_super_admin.md)

## 12. Siguientes mejoras sugeridas para equipo de desarrollo

- Definir plantilla de PR y checklist automatica
- Agregar CI (test + lint + build) en cada PR
- Definir convencion de arquitectura por modulo (Services, Policies, Requests)
- Agregar guia de versionado y release notes

## 13. Definition of Done (DoD)

Un cambio se considera terminado solo si cumple todos estos puntos:

1. Funcionalidad
- El requerimiento implementado esta completo y validado manualmente en flujo real.

2. Calidad tecnica
- No hay errores de sintaxis ni regresiones evidentes.
- El codigo nuevo mantiene consistencia con patrones existentes del modulo.

3. Pruebas
- Incluye pruebas nuevas o actualizadas cuando aplica.
- Pasa al menos la suite del area afectada.
- Si el cambio toca autenticacion, roles o middleware, incluye prueba Feature de permisos.

4. Seguridad y datos
- No expone secretos, tokens o credenciales.
- Si hay cambio de BD, existe migracion valida y reversible cuando sea posible.

5. Documentacion
- Se actualiza documentacion tecnica/operativa cuando el flujo cambia.

6. Entrega
- PR con contexto claro, riesgos, evidencia de pruebas y plan de rollback.

## 14. Matriz de ownership por modulo

Esta matriz define quien debe revisar y aprobar segun area impactada.

| Modulo | Revisor primario | Revisor secundario | Requiere aprobacion dual |
|---|---|---|---|
| Rutas y middleware ([routes/web.php](routes/web.php), [app/Http/Middleware](app/Http/Middleware)) | Backend Lead | QA/Arquitecto | Si |
| Controllers y validacion HTTP ([app/Http/Controllers](app/Http/Controllers)) | Backend Lead | Reviewer de dominio | Si |
| Servicios de negocio/storage ([app/Services](app/Services)) | Backend Senior | Reviewer de infraestructura | Si |
| Modelos y Eloquent ([app/Models](app/Models)) | Backend Lead | DBA/Reviewer BD | Si |
| Migraciones y seeders ([database/migrations](database/migrations), [database/seeders](database/seeders)) | DBA/Backend Senior | Backend Lead | Si |
| Frontend Blade/JS ([resources/views](resources/views), [resources/js](resources/js)) | Frontend Lead | Backend Reviewer | No (si no toca backend) |
| Pruebas ([tests](tests)) | QA/Backend | Autor del modulo | No |
| Documentacion ([README.md](README.md), [GUIA_*.md](GUIA_PRODUCCION.md)) | Tech Lead | Cualquier maintainer | No |

Regla recomendada:
- Si el cambio toca 2 o mas modulos criticos (rutas + DB, o middleware + services), exigir 2 aprobaciones.

## 15. Reglas de Pull Request (nivel equipo)

1. Alcance
- Preferir PRs pequenos y coherentes por objetivo.
- Evitar mezclar refactor grande con fix funcional en la misma PR.

2. Plantilla minima de PR
- Contexto y objetivo
- Cambios principales
- Riesgos tecnicos
- Evidencia de pruebas ejecutadas
- Capturas o payloads de ejemplo (si aplica)
- Plan de rollback

3. Riesgo obligatorio
- Marcar si toca: BD, permisos, autenticacion, archivos, integraciones externas.

4. Bloqueos de merge
- No mergear con checks fallidos.
- No mergear sin reviewer asignado en cambios criticos.

## 16. Flujo de release y rollback

### 16.1 Candidata a release

Una release debe contener:
- Cambios aprobados en branch `release/*`
- Validacion funcional basica (login, dashboard, ticket create/track/close)
- Migraciones revisadas

### 16.2 Hotfix

Para incidentes productivos:
- Crear `hotfix/*`
- Corregir con el menor cambio posible
- Agregar al menos una prueba de regresion
- Merge a rama principal y retroportar a ramas activas

### 16.3 Rollback operativo

Checklist minimo:
- Tener commit/tag anterior identificado
- Confirmar si hubo migracion destructiva
- Ejecutar rollback de codigo y luego limpiar caches:

```powershell
php artisan optimize:clear
php artisan config:cache
php artisan view:cache
```

- Validar endpoints criticos despues de rollback

## 17. Metricas de calidad recomendadas

Objetivos iniciales para el equipo:

1. Cobertura
- Mantener tendencia creciente en coverage global.
- Para modulo tocado, no aceptar caida de cobertura sin justificacion.

2. Revision
- Tiempo objetivo de primera revision: <= 24h habiles.

3. Estabilidad
- Registrar incidentes post-merge por release.
- Priorizar reduccion de regresiones en modulos de permisos y tickets.

4. Salud de PRs
- Reducir PRs demasiado grandes.
- Mantener descripcion completa y evidencia de pruebas en 100% de PRs.

<!-- ACTUALIZACION_JUNIO_2026 -->
## Novedades Funcionales (Junio 2026)

- Carga masiva CSV reforzada con lectura UTF-8 y manejo explicito de comillas dobles como encapsulador de texto.
- Validacion estructural por fila en importaciones CSV para detectar columnas rotas por delimitador/comillas antes de escribir en BD.
- Mejora de importacion de cursos para relacion muchos-a-muchos con programas mediante tabla pivote course_program (manteniendo compatibilidad con program_id legado).
- Carga masiva de cursos con soporte de multiples referencias: program_id/program_ids, program_code/program_codes y program_name/program_names.
- Resolucion de ambiguedades de programas mejorada con filtros por faculty_id/faculty_name e institution_id/institution_name.
- Cuando program_code/program_name es duplicado y no se envia desambiguacion, la importacion puede vincular el curso a todos los programas coincidentes.
- Formularios de crear/editar cursos mejorados con selector multiple con busqueda (Tom Select), conservando compatibilidad del campo program_id.

