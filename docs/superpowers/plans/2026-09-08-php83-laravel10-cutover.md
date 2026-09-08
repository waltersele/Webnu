# PHP 8.3 / Laravel 10 Cutover Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Endurecer `deploy.sh` y la documentación para un corte seguro de producción a Laravel 10 bajo PHP 8.3, sin mezclar vendor L7 con código L10.

**Architecture:** Detección automática de “upgrade mayor” fuerza `--full-rsync` + `composer install`; backup MySQL falla de forma visible; `artisan down` no tumba el deploy si el vendor viejo no arranca; post-check exige `Application::VERSION` 10.x. Docs y reglas Cursor alineadas con PHP 8.3 / `main`.

**Tech Stack:** Bash (`scripts/deploy.sh`), cPanel SSH, Composer, PHP 8.3, Laravel 10, MySQL `mysqldump`.

**Spec:** `docs/superpowers/specs/2026-09-08-php83-laravel10-cutover-design.md`

## Global Constraints

- Producción: PHP 8.3.x, Laravel 10.x, rama `origin/main`.
- Nunca sobrescribir: `.env`, `public/img/`, `public/.htaccess`, `storage/` sensible.
- No subir `vendor/` por FileZilla.
- `scripts/` no se rsync-ea: hay que copiar `deploy.sh` a mano en live antes del primer corte.
- Idioma commits: español; formato `tipo(scope): descripción`.

## File map

| File | Responsibility |
|------|----------------|
| `scripts/deploy.sh` | Deploy endurecido (backup, upgrade mayor, down tolerante, assert L10) |
| `docs/deploy.md` | Guía operativa + sección “Primer corte L10 / PHP 8.3” |
| `.cursor/rules/webnu-deploy.mdc` | Intents push vs deploy; stack actual PHP 8.3 / L10 |
| `README.md` | Requisitos PHP 8.3 / Laravel 10 |

---

### Task 1: Backup MySQL visible y con tamaño mínimo

**Files:**
- Modify: `scripts/deploy.sh` (bloque Backup BD, ~líneas 290–317)

**Interfaces:**
- Consumes: `SKIP_BACKUP`, `BACKUP_DIR`, `SITE_ROOT/.env`, `DRY_RUN`, `die`, `ok`, `warn`, `say`
- Produces: dump `storage/backups/db-YYYYMMDD-HHMM.sql.gz` con tamaño ≥ 1024 bytes, o abort del script

- [ ] **Step 1: Sustituir el bloque de mysqldump**

Reemplazar el `else` interno del backup (donde está `MYSQL_PWD=... mysqldump ... 2>/dev/null`) por:

```bash
        else
            DUMP_ERR="$(mktemp)"
            set +e
            MYSQL_PWD="$DB_PASS" mysqldump --single-transaction --quick \
                -h "$DB_HOST" -u "$DB_USER" "$DB_NAME" 2>"$DUMP_ERR" \
                | gzip > "$BACKUP_FILE"
            DUMP_STATUS=${PIPESTATUS[0]}
            set -e
            if [[ $DUMP_STATUS -ne 0 ]]; then
                warn "mysqldump falló (exit $DUMP_STATUS):"
                cat "$DUMP_ERR" >&2 || true
                rm -f "$DUMP_ERR" "$BACKUP_FILE"
                die "Backup MySQL falló. Usa --skip-backup solo si ya tienes un dump válido, o corrige DB_* en .env"
            fi
            rm -f "$DUMP_ERR"
            BACKUP_BYTES="$(wc -c < "$BACKUP_FILE" | tr -d ' ')"
            if [[ "${BACKUP_BYTES:-0}" -lt 1024 ]]; then
                rm -f "$BACKUP_FILE"
                die "Backup MySQL demasiado pequeño (${BACKUP_BYTES} bytes). Aborto."
            fi
            ok "Backup en $BACKUP_FILE ($(du -h "$BACKUP_FILE" | cut -f1))"
        fi
```

También: si `DB_NAME` está vacío, cambiar de `warn` + salto a:

```bash
        die "No se pudo leer DB_DATABASE de .env; no despliego sin backup. Usa --skip-backup si es intencional."
```

- [ ] **Step 2: Revisar sintaxis bash**

Run (Git Bash o WSL si estás en Windows; en el servidor: `bash -n`):

```bash
bash -n scripts/deploy.sh
```

Expected: sin salida, exit 0.

- [ ] **Step 3: Commit**

```bash
git add scripts/deploy.sh
git commit -m "fix(deploy): backup MySQL visible y con tamaño mínimo"
```

---

### Task 2: Detectar upgrade mayor y forzar full-rsync + vendor

**Files:**
- Modify: `scripts/deploy.sh` (tras `say "PREV=... NEW=..."`, antes del bloque mantenimiento)

**Interfaces:**
- Consumes: `SITE_ROOT`, `REPO_ROOT`, `FULL_RSYNC`, `WITH_VENDOR`
- Produces: `detect_major_upgrade` (exit 0 = sí); puede setear `FULL_RSYNC=1` y `WITH_VENDOR=1`

- [ ] **Step 1: Añadir helpers tras `should_sync_vendor`**

```bash
live_laravel_version() {
    local f="$SITE_ROOT/vendor/laravel/framework/src/Illuminate/Foundation/Application.php"
    if [[ ! -f "$f" ]]; then
        echo ""
        return
    fi
    grep -E "const VERSION = '" "$f" | head -1 | sed -E "s/.*'([^']+)'.*/\1/" || echo ""
}

detect_major_upgrade() {
    # true (return 0) si live no es L10 o composer.lock difiere del mirror
    local live_ver lock_live lock_repo
    live_ver="$(live_laravel_version)"
    if [[ -z "$live_ver" || "$live_ver" != 10.* ]]; then
        return 0
    fi
    lock_live="$SITE_ROOT/composer.lock"
    lock_repo="$REPO_ROOT/composer.lock"
    if [[ ! -f "$lock_live" || ! -f "$lock_repo" ]]; then
        return 0
    fi
    if ! cmp -s "$lock_live" "$lock_repo"; then
        return 0
    fi
    return 1
}
```

- [ ] **Step 2: Tras `say "PREV=$PREV_SHA  NEW=$NEW_SHA"`, forzar flags**

```bash
if detect_major_upgrade; then
    warn "Upgrade mayor detectado (Laravel live≠10.x y/o composer.lock distinto). Forzando --full-rsync --with-vendor."
    FULL_RSYNC=1
    WITH_VENDOR=1
fi
```

Mantener el bloque existente que limpia exclusiones de composer cuando `should_sync_vendor` (sigue siendo útil).

- [ ] **Step 3: En modo delta, no eliminar composer.json/lock de la lista si `should_sync_vendor`**

Cambiar el `sed -i` del delta: quitar las líneas que borran `composer.json`/`composer.lock` cuando vamos a instalar vendor, envolviendo así:

```bash
    if should_sync_vendor; then
        sed -i -E \
            -e '/^\.env$/d' -e '/^\.env\.example$/d' \
            -e '/^php-local\.ini$/d' -e '/^run-local\.ps1$/d' \
            -e '/^public\/img\//d' -e '/^public\/\.htaccess$/d' \
            -e '/^vendor\//d' \
            -e '/^storage\/(logs|framework|backups)\//d' \
            -e '/^(tests|docs|scripts)\//d' \
            -e '/^\.github\//d' \
            -e '/^node_modules\//d' \
            "$CHANGED_TMP" "$DELETED_TMP"
    else
        sed -i -E \
            -e '/^composer\.(json|lock)$/d' \
            -e '/^\.env$/d' -e '/^\.env\.example$/d' \
            -e '/^php-local\.ini$/d' -e '/^run-local\.ps1$/d' \
            -e '/^public\/img\//d' -e '/^public\/\.htaccess$/d' \
            -e '/^vendor\//d' \
            -e '/^storage\/(logs|framework|backups)\//d' \
            -e '/^(tests|docs|scripts)\//d' \
            -e '/^\.github\//d' \
            -e '/^node_modules\//d' \
            "$CHANGED_TMP" "$DELETED_TMP"
    fi
```

- [ ] **Step 4: `bash -n scripts/deploy.sh`**

Expected: exit 0.

- [ ] **Step 5: Commit**

```bash
git add scripts/deploy.sh
git commit -m "fix(deploy): forzar full-rsync y composer en upgrade mayor"
```

---

### Task 3: `artisan down` tolerante + assert Laravel 10

**Files:**
- Modify: `scripts/deploy.sh` (mantenimiento, trap, post-composer)

**Interfaces:**
- Consumes: `NO_DOWN`, `PHP_BIN`, `SITE_ROOT`, `artisan`, `composer_run`
- Produces: `MAINTENANCE=0|1`; `assert_laravel_10` aborta si VERSION no es 10.x

- [ ] **Step 1: Sustituir bloque mantenimiento**

Reemplazar:

```bash
# ---------- Mantenimiento --------------------------------------------------- #
if [[ $NO_DOWN -eq 0 ]]; then
    artisan "down --retry=30 --message='Desplegando mejoras' || true"
fi

# Aseguramos un `up` si algo falla
trap 'if [[ $NO_DOWN -eq 0 ]]; then $PHP_BIN $SITE_ROOT/artisan up >/dev/null 2>&1 || true; fi' EXIT
```

por:

```bash
# ---------- Mantenimiento --------------------------------------------------- #
MAINTENANCE=0
if [[ $NO_DOWN -eq 0 ]]; then
    say "Intentando modo mantenimiento"
    if [[ $DRY_RUN -eq 1 ]]; then
        dry_say "$PHP_BIN $SITE_ROOT/artisan down ..."
        MAINTENANCE=1
    else
        set +e
        "$PHP_BIN" "$SITE_ROOT/artisan" down --retry=30 --message='Desplegando mejoras'
        DOWN_STATUS=$?
        set -e
        if [[ $DOWN_STATUS -eq 0 ]]; then
            MAINTENANCE=1
            ok "Sitio en mantenimiento"
        else
            warn "artisan down falló (exit $DOWN_STATUS). Continuando: probable vendor incompatible; se regenerará."
        fi
    fi
fi

trap 'if [[ ${MAINTENANCE:-0} -eq 1 ]]; then $PHP_BIN $SITE_ROOT/artisan up >/dev/null 2>&1 || true; fi' EXIT
```

Y al cierre, reemplazar `if [[ $NO_DOWN -eq 0 ]]; then artisan "up"; fi` por:

```bash
if [[ $MAINTENANCE -eq 1 ]]; then
    artisan "up"
fi
```

- [ ] **Step 2: Añadir `assert_laravel_10` y llamarla tras `composer install`**

Tras el bloque `if should_sync_vendor; then composer_run install...`:

```bash
assert_laravel_10() {
    local ver
    ver="$(live_laravel_version)"
    if [[ "$ver" != 10.* ]]; then
        die "Se esperaba Laravel 10.x tras composer install; encontrado: '${ver:-desconocido}'. Aborto sin marcar SHA."
    fi
    ok "Laravel VERSION=$ver"
}

if should_sync_vendor; then
    say "Instalando dependencias (composer install --no-dev)"
    composer_run "install --no-dev --no-interaction --prefer-dist --optimize-autoloader"
    if [[ $DRY_RUN -eq 0 ]]; then
        assert_laravel_10
    fi
else
    ...
fi
```

- [ ] **Step 3: `bash -n scripts/deploy.sh`**

Expected: exit 0.

- [ ] **Step 4: Commit**

```bash
git add scripts/deploy.sh
git commit -m "fix(deploy): down tolerante y assert Laravel 10 tras composer"
```

---

### Task 4: Documentación operativa (`docs/deploy.md`)

**Files:**
- Modify: `docs/deploy.md`

**Interfaces:**
- Consumes: comportamiento de Tasks 1–3
- Produces: sección “Primer corte L10 / PHP 8.3” + troubleshooting de errores ya vistos

- [ ] **Step 1: Actualizar arquitectura**

En “Arquitectura del hosting”, asegurar:

- Stack: PHP 8.3, Laravel 10.x (ya está en parte del doc; eliminar cualquier resto “vendor instalado con PHP 7.4, no se sincroniza” → indicar que `vendor/` se regenera con `composer install` en el servidor).

- [ ] **Step 2: Añadir sección tras “Flags útiles”**

```markdown
## Primer corte / upgrade mayor (Laravel 7 → 10, PHP 8.3)

**Antes (una vez):** copiar el script nuevo (el rsync excluye `scripts/`):

```bash
cp ~/webnu-deploy/scripts/deploy.sh ~/public_html/webnu.es/scripts/deploy.sh
chmod +x ~/public_html/webnu.es/scripts/deploy.sh
```

**Corte:**

```bash
cd /home/wwwwebnu/public_html/webnu.es
# Opcional: dump manual previo
./scripts/deploy.sh --full-rsync --with-vendor
```

Si el backup automático falla y ya tienes dump válido: añade `--skip-backup`.

El script detecta upgrade mayor solo (live ≠ Laravel 10 o `composer.lock` distinto) y fuerza full-rsync + composer aunque olvides los flags.

**Verificación:**

```bash
grep -n "VERSION =" vendor/laravel/framework/src/Illuminate/Foundation/Application.php | head -1
php artisan --version
curl -sI https://webnu.es/ | head -5
curl -sI https://webnu.es/login | head -5
```

Esperado: `VERSION = '10.x.x'`, HTTP 200, sin `Fideloper` / `Elibyy`.
```

- [ ] **Step 3: Añadir troubleshooting**

```markdown
### `Collection::offsetExists` / ArrayAccess (PHP 8.3 + Laravel 7)

Vendor L7 bajo PHP 8.3. Solución: `./scripts/deploy.sh --full-rsync --with-vendor` (o dejar que `detect_major_upgrade` lo fuerce). No bajes a PHP 7.4.

### `Class Elibyy\TCPDF\ServiceProvider not found`

Cache de packages L7. `rm -f bootstrap/cache/{packages,services,config}.php && php artisan package:discover`.

### `Class Fideloper\Proxy\TrustProxies not found`

Código `app/` aún L7. Forzar full-rsync del mirror L10; `TrustProxies` debe usar `Illuminate\Http\Middleware\TrustProxies`.

### Deploy se corta en «Backup MySQL» sin mensaje

Versiones antiguas del script ocultaban el error. Actualiza `deploy.sh` y reintenta; o `--skip-backup` con dump manual previo.
```

- [ ] **Step 4: Commit**

```bash
git add docs/deploy.md
git commit -m "docs(deploy): primer corte L10/PHP 8.3 y troubleshooting"
```

---

### Task 5: Reglas Cursor + README

**Files:**
- Modify: `.cursor/rules/webnu-deploy.mdc`
- Modify: `README.md` (sección Requisitos)

**Interfaces:**
- Consumes: stack L10 / PHP 8.3 en `main`
- Produces: reglas sin “PHP 7.4” ni “no desplegar upgrade/laravel-10”

- [ ] **Step 1: Reescribir mapa rápido en `webnu-deploy.mdc`**

Sustituir el bloque “Mapa rápido” y la sección “Cambios en composer.json” por:

```markdown
## Mapa rápido del despliegue

- Repo GitHub: `https://github.com/waltersele/Webnu`
- Rama producción: `main` (Laravel 10 / PHP ^8.3)
- Host: `webnu.es` (cPanel, PHP 8.3, MySQL)
- Usuario SSH: `wwwwebnu`
- Clon mirror: `/home/wwwwebnu/webnu-deploy/`
- Sitio live: `/home/wwwwebnu/public_html/webnu.es/`
- Script: `scripts/deploy.sh` (`--dry-run`, `--rollback`, `--skip-migrate`, `--skip-backup`, `--no-down`, `--full-rsync`, `--with-vendor`)
- Documentación: `docs/deploy.md`
- Primer corte / vendor desfasado: `./scripts/deploy.sh --full-rsync --with-vendor` (y copiar antes `scripts/deploy.sh` a live)

## Cambios en `composer.json`

Producción es PHP 8.3 / Laravel 10. Los cambios de dependencias van en `main` y se aplican en servidor con `composer install` vía `deploy.sh` (upgrade mayor o `--with-vendor`). Nunca subir `vendor/` por FTP.
```

En el comando de deploy “normal”, dejar el default; para upgrades mencionar en un comentario del intent “despliega” que si hay salto de framework hay que usar `--full-rsync --with-vendor`.

Eliminar referencias a rama `upgrade/laravel-10` como “NO desplegar”.

- [ ] **Step 2: Actualizar `README.md` Requisitos**

```markdown
## Requisitos

- PHP 8.3+ (Laravel 10)
- Composer, Node.js (assets con Laravel Mix)
- SQLite o MySQL para la base de datos
- Opcional: Tesseract (OCR fallback), API Gemini (escaneo IA)
```

- [ ] **Step 3: Commit**

```bash
git add .cursor/rules/webnu-deploy.mdc README.md
git commit -m "docs: alinear README y regla deploy a PHP 8.3 / Laravel 10"
```

---

### Task 6: Verificación local

**Files:**
- Test only (no create): `tests/Feature/MeasurementSettingsTest.php` y suite general

**Interfaces:**
- Consumes: árbol L10 local ya instalado
- Produces: evidencia de boot + tests verdes antes del push/corte

- [ ] **Step 1: Confirmar versiones locales**

```bash
cd Webnu
php -v
php artisan --version
grep -n "VERSION =" vendor/laravel/framework/src/Illuminate/Foundation/Application.php | head -1
```

Expected: PHP 8.3.x, Laravel Framework 10.x, `VERSION = '10....'`.

- [ ] **Step 2: Tests**

```bash
php artisan test --filter=MeasurementSettingsTest
```

Expected: PASS. Si el entorno local no tiene DB de test, documentar el fallo y al menos confirmar `php artisan about` / `php artisan route:list` sin error.

- [ ] **Step 3: `bash -n scripts/deploy.sh` otra vez**

Expected: exit 0.

- [ ] **Step 4: Commit vacío no; si hubo fixes menores, commit `fix(deploy): ...`**

---

### Task 7: Corte en producción (operación, con el usuario)

**Files:**
- None in repo (operación SSH). El agente **imprime** comandos; el usuario los ejecuta (regla `webnu-deploy.mdc`).

**Interfaces:**
- Consumes: `main` pusheado con Tasks 1–5
- Produces: live en Laravel 10; criterios de éxito de la spec

- [ ] **Step 1: Push a GitHub**

```bash
git push origin main
```

- [ ] **Step 2: Imprimir / ejecutar en SSH (usuario)**

```bash
cd /home/wwwwebnu/webnu-deploy && git fetch origin && git reset --hard origin/main
cp ~/webnu-deploy/scripts/deploy.sh ~/public_html/webnu.es/scripts/deploy.sh
chmod +x ~/public_html/webnu.es/scripts/deploy.sh
cd /home/wwwwebnu/public_html/webnu.es
./scripts/deploy.sh --full-rsync --with-vendor
```

Si backup falla con credenciales pero hay dump reciente: añadir `--skip-backup`.

- [ ] **Step 3: Verificación**

```bash
grep -n "VERSION =" vendor/laravel/framework/src/Illuminate/Foundation/Application.php | head -1
php artisan --version
curl -sI https://webnu.es/ | head -5
curl -sI https://webnu.es/login | head -5
curl -sI https://webnu.es/carta/demo | head -5
```

Expected: Laravel 10.x, HTTP 200 en home/login/carta.

- [ ] **Step 4: Si falla — rollback (usuario)**

Según spec: restore dump BD del backup del corte + restaurar árbol que “iba bien” (backup cPanel) + limpiar bootstrap cache. Documentar resultado en el chat.

---

## Spec coverage (self-review)

| Spec | Task |
|------|------|
| Backup visible + tamaño mínimo | Task 1 |
| Upgrade mayor → full-rsync + composer | Task 2 |
| Delta no pierde composer.lock en stack moderno | Task 2 |
| down tolerante / trap por MAINTENANCE | Task 3 |
| assert VERSION 10.x | Task 3 |
| docs/deploy.md corte + troubleshooting | Task 4 |
| webnu-deploy.mdc + README | Task 5 |
| Verificación local | Task 6 |
| Corte prod + criterios éxito + rollback | Task 7 |
| No FileZilla vendor / preservar .env e img | Global + docs |

## Placeholder scan

Sin TBD / “implement later” / pasos vagos.
