# Diseño: corte seguro a PHP 8.3 / Laravel 10 (webnu.es)

Fecha: 2026-09-08  
Estado: aprobado por producto (enfoque 1)  
Repo: `Webnu/Webnu` (`main`)

## Problema

Producción (`webnu.es`) corre en **PHP 8.3** con una copia restaurada que **funciona**, pero el código canónico en `main` ya es **Laravel 10 / PHP ^8.3**. Un intento previo de corte falló por:

1. `deploy.sh` llamaba a `artisan` / fallaba en backup MySQL **antes** de regenerar `vendor`.
2. Sync incompleto: código L10 + vendor L7 (o caches/providers L7).
3. Errores en cascada (`Collection`/`ArrayAccess`, `Elibyy\TCPDF`, `Fideloper\Proxy`).

Objetivo: dejar producción alineada con `main` (Laravel 10 + PHP 8.3) **sin** mezclar stacks y **sin** perder `.env`, `public/img/` ni cartas/QR.

## Fuera de alcance

- Features nuevas de producto (medición, UI, etc.), salvo lo ya en `main`.
- Staging permanente / blue-green en cPanel.
- Subir `vendor/` por FileZilla.
- Bajar PHP a 7.4.

## Arquitectura del corte

```
~/webnu-deploy/          ← git mirror origin/main (Laravel 10)
        │
        │  deploy.sh endurecido
        │  (full-rsync + composer install)
        ▼
~/public_html/webnu.es/  ← live (document root: .../public/)
   .env, public/img/, storage/  → NUNCA sobrescribir
   vendor/                      → regenerar con composer en servidor
```

Stack objetivo live:

| Componente | Valor |
|------------|--------|
| PHP (cPanel MultiPHP + CLI) | 8.3.x |
| Laravel | 10.x (`Illuminate\Foundation\Application::VERSION`) |
| Composer | `install --no-dev --optimize-autoloader` en el servidor |
| Rama | `origin/main` |

## Componentes a cambiar

### 1. `scripts/deploy.sh` (endurecimiento)

| Fallo previo | Cambio |
|--------------|--------|
| `mysqldump` con stderr a `/dev/null` + `pipefail` aborta sin mensaje | Mostrar errores de dump; si falla → `die` con causa. `--skip-backup` sigue omitiendo el paso a propósito. Validar tamaño mínimo del `.sql.gz` (p. ej. > 1 KiB). |
| Delta rsync excluye siempre `composer.json`/`lock` | Si detecta **upgrade mayor** (ver abajo) → forzar comportamiento equivalente a `--full-rsync` + `composer install`. |
| `artisan down` con vendor L7 bajo PHP 8.3 tumba el script | Si `artisan down` falla: avisar y continuar (o poner maintenance solo tras `composer install` exitoso). Trap de `up` solo si se entró en mantenimiento. |
| Caches de providers L7 | Tras rsync: borrar siempre `bootstrap/cache/{config,services,packages}.php` **antes** de `composer install` / `package:discover`. |
| Sin verificación de versión | Tras install: comprobar que `Application::VERSION` empieza por `10.`; si no → `die`. |

**Detección de upgrade mayor** (cualquiera implica full sync + vendor):

- Flag explícito `--full-rsync` o `--with-vendor`, **o**
- En live, `vendor/.../Application.php` tiene `VERSION` que no empieza por `10.`, **o**
- `composer.lock` del mirror difiere del de live (hash/mtime o `cmp`).

Orden obligatorio en upgrade mayor:

1. (Opcional) intentar `artisan down`; si falla, continuar.
2. Backup BD (salvo `--skip-backup`).
3. `rsync` completo (excluyendo `.env`, `public/img/`, `storage/` sensible, etc.).
4. Limpiar bootstrap cache.
5. `composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader`.
6. `package:discover` → `migrate --force` → clear/cache → `webnu:deploy-check`.
7. Guardar SHA → `artisan up` (si hubo down).

**Nota:** `scripts/` está en `EXCLUDES` del rsync. El `deploy.sh` nuevo debe copiarse a live **una vez a mano** antes del corte (o documentar `scp`/`cp` desde `~/webnu-deploy/scripts/deploy.sh`).

### 2. Documentación

- Actualizar `docs/deploy.md`: stack PHP 8.3 / L10; procedimiento de **primer corte / upgrade mayor**; no subir vendor por FTP; flags `--full-rsync --with-vendor`; troubleshooting de los errores ya vistos.
- Actualizar `.cursor/rules/webnu-deploy.mdc` y menciones en `README.md` que aún digan PHP 7.4 / “no composer en prod”.

### 3. Verificación local (pre-corte)

- PHP ≥ 8.3, `composer install`, `php artisan --version` → Laravel 10.
- Suite de tests razonable en CI/local (como mínimo tests de medición y smoke de arranque si existen).
- No se requiere dump de prod en este diseño si el tiempo urge; recomendado si hay migraciones destructivas (hoy el riesgo principal es vendor/código, no schema).

## Flujo de datos / secuencia operativa

```
[Local] push main (si hay fixes de deploy)
    → [SSH] cp deploy.sh nuevo a SITE_ROOT/scripts/
    → backup MySQL manual (opcional reforzado)
    → ./scripts/deploy.sh --full-rsync --with-vendor
    → checks HTTP + artisan --version
    → [si falla] rollback: restore código backup + vendor L7 o reverse rsync + restore DB
```

## Manejo de errores

| Situación | Acción |
|-----------|--------|
| Backup MySQL falla | Abortar deploy; sitio intacto (si aún no rsync). |
| `composer install` falla | Abortar; no ejecutar migrate; dejar maintenance si aplica; restaurar vendor desde backup o re-ejecutar install. |
| `migrate` falla | Abortar; no `up` automático ciego; operador decide restore DB. |
| Post-check VERSION ≠ 10 | `die`; no marcar SHA como desplegado. |
| Class not found (provider viejo) | Limpiar bootstrap cache + `package:discover`; si el fichero app es L7, re-rsync `app/`. |

## Criterios de éxito (producción)

1. `curl -sI https://webnu.es/` → HTTP 200 (no 500).
2. Login y al menos una carta pública (`/carta/{slug}`) OK.
3. `php artisan --version` → Laravel Framework 10.x.
4. `grep VERSION vendor/laravel/framework/.../Application.php` → `10.`.
5. Sin referencias runtime a `Fideloper\Proxy` ni `Elibyy\TCPDF`.
6. `.env` y `public/img/` intactos.

## Rollback

1. Poner mantenimiento si es posible.
2. Restaurar dump MySQL del backup del corte (si hubo migrate).
3. Restaurar árbol de aplicación desde backup cPanel/FileZilla o re-rsync de la copia que “iba bien”.
4. `composer install` coherente con ese árbol **o** restaurar `vendor/` del backup.
5. Limpiar bootstrap cache → `artisan up`.
6. Confirmar HTTP 200.

## Testing

- Local: artisan boot + tests PHPUnit existentes tras cambios a `deploy.sh` (el script en sí se valida en dry-run / checklist, no unit tests obligatorios).
- Prod: checklist de criterios de éxito arriba + `php artisan webnu:deploy-check`.

## Riesgos residuales

- Ventana de downtime durante `composer install` (varios minutos).
- `deploy.sh` en live desactualizado si se olvida copiarlo antes del primer corte.
- Si MultiPHP web ≠ CLI PHP, Composer podría instalar para otra versión: el script debe usar el mismo `PHP_BIN` 8.3 que cPanel.
