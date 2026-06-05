# Session Summary — Jun 4, 2026

## Problems Solved

### 1. SQL `pluck('proyecto_id')` bypassed `MapsLegacyColumns`
- **File**: `app/Livewire/PublicarProyectoManager.php` (now deleted)
- **Fix**: Replaced `->pluck('proyecto_id')` with `->get()->pluck('proyecto_id')` so model accessors convert `id → pry_codigo`.
- **Rule**: `select()` and `pluck()` on Query Builder bypass `MapsLegacyColumns`. Use `get()->pluck()` on Collection instead.

### 2. `pry_titulo` column doesn't exist
- **Schema**: Removed `titulo → pry_titulo` from `config/repositorio_schema.php`.
- **Model**: Removed `titulo` from `$fillable` in `app/Models/Proyecto.php`.
- **Accessor**: Added `getTituloAttribute()` that reads `grp_nombre` from `grupo_proyecto_modulo` via `equipo_ref` (pattern: `EQGRP:{id}`).
- **6 search queries fixed**: Changed `where('titulo', 'like', ...)` → `where('resumen', 'like', ...)` in:
  - `app/Models/Proyecto.php` (scope)
  - `app/Services/ProyectoGestionService.php`
  - `app/Services/ProyectoBusquedaService.php`
  - `app/Livewire/PublicarProyectoManager.php` (deleted)

### 3. `com_nombre_encargado`, `com_apellido_encargado`, `com_telefono_encargado` NOT NULL with no default
- **Schema**: Added `nombre_encargado → com_nombre_encargado`, `apellido_encargado → com_apellido_encargado`, `telefono_encargado → com_telefono_encargado`, `anio → com_anio` to `config/repositorio_schema.php`.
- **Model**: Added to `$fillable` in `app/Models/Comunidad.php`.
- **Component**: Added properties, rules, save logic in `app/Livewire/ComunidadManager.php`.
- **View**: Added form fields.
- **Fallback**: Insert/update uses `'-'` default if empty.

### 4. Migration: `uex_codigo` nullable + `cop_nombre_contacto` column
- **File**: `database/migrations/2026_06_04_110000_modify_comentarios_proyecto_table.php`
- **Status**: ✅ Run (ran before MySQL went down)

### 5. Migration: `org_correo` column
- **File**: `database/migrations/2026_06_04_120000_add_org_correo_to_organizacion_table.php`
- **Status**: ⏳ NOT RUN (MySQL unavailable)
- **Schema mapping**: `correo → org_correo` already added to `config/repositorio_schema.php`
- **Model**: `correo` added to `$fillable` in `app/Models/Organizacion.php`
- **Component**: `org_correo` property, rules, save, edit, reset all updated
- **View**: form field added to `resources/views/livewire/organizacion-manager.blade.php`

## Removed

### `PublicarProyectoManager` (Publicar Proyectos)
- Deleted:
  - `app/Livewire/PublicarProyectoManager.php`
  - `resources/views/livewire/publicar-proyecto-manager.blade.php`
  - `resources/views/livewire/publicar-proyecto-manager-wrapper.blade.php`
- Route removed: `/publicaciones/publicar`
- Sidebar link removed: "Publicar Proyectos"

## Added

### `ProyectosPublicosManager` (Vista Pública)
- **File**: `app/Livewire/ProyectosPublicosManager.php`
- **View**: `resources/views/livewire/proyectos-publicos-manager.blade.php`
- **Route**: `/publicaciones/publico` (Livewire, no auth middleware)
- **Sidebar link**: "Vista Pública" under Publicaciones
- Anonymous users view published projects + comment with name field (`nombreContacto` → `cop_nombre_contacto`)

### `ProjectSearch` — "Enviar a organización"
- **Component**: `app/Livewire/ProjectSearch.php` — added `$selectedProjects` (array), `$showEnvioModal`, `$orgSeleccionadas`, methods: `toggleProject()`, `abrirEnvio()`, `enviarCorreo()`, `cuerpoCorreo()`
- **View**: Checkboxes in search results, modal with org selection and project list, send button
- Uses `Mail::raw()` with `log` mailer (configurable via `.env`)

### `GenerateLoginLink` — force intranet connection
- **File**: `app/Console/Commands/GenerateLoginLink.php`
- Changed `DbHelper::connection()` → hardcoded `'intranet'` so magic link generation only searches PostgreSQL intranet, no simulation fallback.

## Blockers
- MySQL (`127.0.0.1:3306`) unreachable — migrate `2026_06_04_120000_add_org_correo_to_organizacion_table` must run when MySQL is available.
- Intranet PostgreSQL unreachable — can't test `GenerateLoginLink`.
- Mail is configured as `log` driver — emails are written to `storage/logs/laravel.log` instead of sent.

## Key Patterns
- `MapsLegacyColumns` trait only works on Model instances (after `get()`). The `LegacyColumnBuilder` only overrides `where()` and `orderBy()` — all other QB methods (`whereIn`, `whereNotNull`, `whereNull`, `pluck`, `select`, `groupBy`, `update`, `delete`, etc.) bypass the mapping.
- **Fix rule**: For `whereIn()`, `whereNotNull()`, `whereNull()` — use the **physical column name** directly (e.g., `'org_dep_codigo'` instead of `'dep_codigo'` on Organizacion).
- **Fix rule**: For `update()` on QB — fetch model instance first, then `->fill($data)->save()`.
- **Fix rule**: For `pluck()` on QB — use `->get()->pluck('col')` (Collection pluck uses model accessor).
- **Fix rule**: For `select()` on QB — use physical column names, or fetch model and access attributes.
- **Physical column names for Organizacion**: `nombre` → `org_nombre`, `correo` → `org_correo`, `dep_codigo` → `org_dep_codigo`, `rif` → `org_rif`, `direccion` → `org_direccion`, `cargo` → `org_cargo`, `id` → `org_codigo`, `nombre_contacto` → `org_nombre_contacto`, etc.
- **Physical column names for Departamento**: `nombre` → `dep_nombre`, `cargo` → `dep_cargo`, `id` → `dep_codigo`, `nombre_contacto` → `dep_nombre_contacto`, etc.
- **Physical column names for Proyecto**: `id` → `pry_codigo`, `equipo_ref` → `pry_direccion_logica`, `estado_validacion` → `pry_estado_validacion`, etc.
- `titulo` is now a computed accessor (not a DB column) reading `grupo_proyecto_modulo.grp_nombre` via `equipo_ref`.
- Anonymous comments store name in `cop_nombre_contacto`; `uex_codigo` nullable.
- Organización `correo` maps to `org_correo` (varchar 255, nullable).

## Bugs Fixed — QB Bypass (Jun 4)

### `OrganizacionManager.php`
| Line | What | Fix |
|------|------|-----|
| 227 | `::where()->update($payload)` | → `->first()->fill($payload)->save()` |
| 261 | `whereNotNull('dep_codigo')` | → `whereNotNull('org_dep_codigo')` |
| 263 | `whereNull('dep_codigo')` | → `whereNull('org_dep_codigo')` |
| 268 | `whereNull('dep_codigo')` | → `whereNull('org_dep_codigo')` |
| 428 | `::where()->update($depData)` | → `->first()->fill($depData)->save()` |
| 444 | `whereNull('dep_codigo')` | → `whereNull('org_dep_codigo')` |
| 467 | `whereNotNull('dep_codigo')` | → `whereNotNull('org_dep_codigo')` |
| 539-541 | `whereNotNull('dep_codigo')->pluck('dep_codigo')` | → `whereNotNull('org_dep_codigo')->get()->pluck('dep_codigo')` |
| 543 | `whereIn('id', ...)` | → `whereIn('dep_codigo', ...)` |

### `ProjectSearch.php`
| Line | What | Fix |
|------|------|-----|
| 91 | `whereIn('dep_codigo', ...)` | → `whereIn('org_dep_codigo', ...)` |
| 99 | `whereIn('id', ...)` | → `whereIn('pry_codigo', ...)` |
| 238-240 | `whereNotNull('correo')` | Removed (covered by `where('correo', '!=', '')`) |

### `ProyectoBusquedaService.php`
| Line | What | Fix |
|------|------|-----|
| 215 | `whereIn('equipo_ref', ...)` | → `whereIn('pry_direccion_logica', ...)` |

### `ProyectoGestionService.php`
| Line | What | Fix |
|------|------|-----|
| 86 | `whereIn('equipo_ref', ...)` | → `whereIn('pry_direccion_logica', ...)` |
