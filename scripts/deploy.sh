#!/usr/bin/env bash
# Webnu — Script de despliegue desde GitHub al sitio live de webnu.es
#
# Pensado para cPanel + SSH. Históricamente PHP 7.4; desde Laravel 10 requiere PHP 8.3+.
# Estructura asumida:
#   $HOME/webnu-deploy/                ← clon git de origin/main
#   $HOME/public_html/webnu.es/        ← sitio live (la aplicación Laravel)
#
# Uso:
#   ./scripts/deploy.sh                # despliegue normal
#   ./scripts/deploy.sh --dry-run      # muestra qué cambiaría sin tocar nada
#   ./scripts/deploy.sh --rollback     # vuelve al SHA del despliegue anterior
#   ./scripts/deploy.sh --skip-migrate # no ejecuta migraciones
#   ./scripts/deploy.sh --skip-backup  # no hace dump de la BD
#   ./scripts/deploy.sh --no-down      # no entra en mantenimiento (deploy en caliente)
#   ./scripts/deploy.sh --full-rsync   # rsync completo en lugar de delta git
#
# Lanzar desde la raíz del sitio:
#   cd $HOME/public_html/webnu.es && ./scripts/deploy.sh

set -euo pipefail

# ---------- Configuración --------------------------------------------------- #
SITE_ROOT="${SITE_ROOT:-$HOME/public_html/webnu.es}"
REPO_ROOT="${REPO_ROOT:-$HOME/webnu-deploy}"
REPO_REMOTE="${REPO_REMOTE:-origin}"
REPO_BRANCH="${REPO_BRANCH:-main}"
PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"
STATE_FILE="${STATE_FILE:-$HOME/.webnu-last-deploy-sha}"
LOG_FILE="${LOG_FILE:-$SITE_ROOT/storage/backups/deploys.log}"
BACKUP_DIR="${BACKUP_DIR:-$SITE_ROOT/storage/backups}"

# Seeders permitidos en producción (whitelist). Si están entre los cambios,
# el script los ejecuta automáticamente tras `migrate`.
PROD_SAFE_SEEDERS=(
    "ProductionDemoSeeder"
)

# Rutas que NUNCA se sincronizan (uploads de cliente, config local, etc.).
EXCLUDES=(
    --exclude='.git'
    --exclude='.github'
    --exclude='node_modules'
    --exclude='tests'
    --exclude='docs'
    --exclude='scripts'
    --exclude='.env'
    --exclude='.env.example'
    --exclude='php-local.ini'
    --exclude='run-local.ps1'
    # En PHP 7.4 se excluían composer/vendor. Desde Laravel 10 (PHP 8.3+) deben sincronizarse.
    --exclude='storage/logs/'
    --exclude='storage/framework/sessions/'
    --exclude='storage/framework/cache/'
    --exclude='storage/framework/views/'
    --exclude='storage/backups/'
    --exclude='public/img/'
    --exclude='public/.htaccess'
    --exclude='public/storage'
)

# ---------- Flags ----------------------------------------------------------- #
DRY_RUN=0
ROLLBACK=0
SKIP_MIGRATE=0
SKIP_BACKUP=0
NO_DOWN=0
FULL_RSYNC=0
WITH_VENDOR=0

while [[ $# -gt 0 ]]; do
    case "$1" in
        --dry-run)      DRY_RUN=1 ;;
        --rollback)     ROLLBACK=1 ;;
        --skip-migrate) SKIP_MIGRATE=1 ;;
        --skip-backup)  SKIP_BACKUP=1 ;;
        --no-down)      NO_DOWN=1 ;;
        --full-rsync)   FULL_RSYNC=1 ;;
        --with-vendor)  WITH_VENDOR=1 ;;
        -h|--help)
            sed -n '2,25p' "$0"
            exit 0
            ;;
        *)
            echo "Opción desconocida: $1" >&2
            exit 2
            ;;
    esac
    shift
done

# ---------- Helpers --------------------------------------------------------- #
C_RESET=$'\e[0m'; C_BLUE=$'\e[34m'; C_GREEN=$'\e[32m'; C_YELLOW=$'\e[33m'; C_RED=$'\e[31m'
say()  { printf "${C_BLUE}→${C_RESET} %s\n" "$*"; }
ok()   { printf "${C_GREEN}✓${C_RESET} %s\n" "$*"; }
warn() { printf "${C_YELLOW}!${C_RESET} %s\n" "$*"; }
die()  { printf "${C_RED}✗${C_RESET} %s\n" "$*" >&2; exit 1; }

dry_say() {
    if [[ $DRY_RUN -eq 1 ]]; then
        printf "  ${C_YELLOW}(dry)${C_RESET} %s\n" "$*"
    fi
}

run() {
    if [[ $DRY_RUN -eq 1 ]]; then
        dry_say "$*"
    else
        eval "$@"
    fi
}

log_line() {
    local ts; ts="$(date '+%Y-%m-%d %H:%M:%S')"
    mkdir -p "$(dirname "$LOG_FILE")"
    echo "[$ts] $*" >> "$LOG_FILE"
}

artisan() {
    run "$PHP_BIN $SITE_ROOT/artisan $*"
}

# Detecta si estamos en stack Laravel 10+ (PHP >= 8.1) para permitir composer/vendor.
should_sync_vendor() {
    if [[ $WITH_VENDOR -eq 1 ]]; then
        return 0
    fi
    local ver
    ver="$($PHP_BIN -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || echo '7.4')"
    case "$ver" in
        8.*) return 0 ;;
        *)   return 1 ;;
    esac
}

# ---------- Comprobaciones previas ----------------------------------------- #
[[ -d "$SITE_ROOT" ]]  || die "No existe SITE_ROOT: $SITE_ROOT"
[[ -d "$REPO_ROOT" ]]  || die "No existe REPO_ROOT: $REPO_ROOT (clona el repo en \$HOME/webnu-deploy)"
[[ -d "$REPO_ROOT/.git" ]] || die "$REPO_ROOT no es un repositorio git"
command -v rsync >/dev/null      || die "rsync no está instalado"
command -v "$PHP_BIN" >/dev/null  || die "$PHP_BIN no está en el PATH"

# ---------- ROLLBACK ------------------------------------------------------- #
if [[ $ROLLBACK -eq 1 ]]; then
    [[ -f "$STATE_FILE" ]] || die "No hay .webnu-last-deploy-sha; nada que rollbackear"
    PREV="$(cat "$STATE_FILE")"
    say "Rollback al SHA $PREV"
    cd "$REPO_ROOT"
    CURRENT="$(git rev-parse HEAD)"
    if [[ "$CURRENT" = "$PREV" ]]; then
        warn "El repo ya está en $PREV; ¿buscas un rollback más antiguo? Hazlo a mano."
        exit 0
    fi
    [[ $NO_DOWN -eq 1 ]] || artisan "down --retry=15"
    run "cd $REPO_ROOT && git fetch --quiet $REPO_REMOTE && git reset --hard $PREV"
    run "rsync -av ${EXCLUDES[*]} $REPO_ROOT/ $SITE_ROOT/"
    run "rm -f $SITE_ROOT/bootstrap/cache/services.php $SITE_ROOT/bootstrap/cache/packages.php $SITE_ROOT/bootstrap/cache/config.php"
    run "cd $SITE_ROOT && $COMPOSER_BIN dump-autoload --no-scripts --optimize --no-interaction --ignore-platform-reqs"
    artisan "package:discover"
    artisan "view:clear"
    artisan "config:cache"
    [[ $NO_DOWN -eq 1 ]] || artisan "up"
    log_line "ROLLBACK a $PREV (desde $CURRENT)"
    ok "Rollback completo a $PREV"
    exit 0
fi

# ---------- Pull del repo --------------------------------------------------- #
say "Sincronizando $REPO_ROOT con $REPO_REMOTE/$REPO_BRANCH"
PREV_SHA_RAW=""
[[ -f "$STATE_FILE" ]] && PREV_SHA_RAW="$(tr -d '[:space:]' < "$STATE_FILE")"

if [[ -z "$PREV_SHA_RAW" ]]; then
    PREV_SHA_RAW="$(cd "$REPO_ROOT" && git rev-parse HEAD 2>/dev/null || echo "")"
fi

# Normalizar a SHA largo (40 chars) para que la comparación sea estable.
PREV_SHA=""
if [[ -n "$PREV_SHA_RAW" ]]; then
    PREV_SHA="$(cd "$REPO_ROOT" && git rev-parse --verify "${PREV_SHA_RAW}^{commit}" 2>/dev/null || echo "")"
fi

run "cd $REPO_ROOT && git fetch --quiet $REPO_REMOTE && git reset --hard $REPO_REMOTE/$REPO_BRANCH"
NEW_SHA="$(cd "$REPO_ROOT" && git rev-parse HEAD)"

if [[ "$PREV_SHA" = "$NEW_SHA" ]]; then
    warn "El SHA local ($PREV_SHA) ya coincide con $REPO_REMOTE/$REPO_BRANCH. Nada que desplegar."
    if [[ $FULL_RSYNC -eq 0 ]]; then
        exit 0
    fi
    warn "Continuando porque --full-rsync fue solicitado."
fi

say "PREV=$PREV_SHA  NEW=$NEW_SHA"

# En Laravel 10+ necesitamos composer.json/lock y vendor.
if should_sync_vendor; then
    # Quitamos exclusiones heredadas.
    EXCLUDES=(${EXCLUDES[@]/--exclude='composer.json'/})
    EXCLUDES=(${EXCLUDES[@]/--exclude='composer.lock'/})
    EXCLUDES=(${EXCLUDES[@]/--exclude='vendor/'/})
fi

# ---------- Mantenimiento --------------------------------------------------- #
if [[ $NO_DOWN -eq 0 ]]; then
    artisan "down --retry=30 --message='Desplegando mejoras' || true"
fi

# Aseguramos un `up` si algo falla
trap 'if [[ $NO_DOWN -eq 0 ]]; then $PHP_BIN $SITE_ROOT/artisan up >/dev/null 2>&1 || true; fi' EXIT

# ---------- Backup BD ------------------------------------------------------- #
if [[ $SKIP_BACKUP -eq 0 ]]; then
    say "Backup MySQL"
    mkdir -p "$BACKUP_DIR"
    STAMP="$(date +%Y%m%d-%H%M)"
    BACKUP_FILE="$BACKUP_DIR/db-$STAMP.sql.gz"

    DB_NAME="$(grep -E '^DB_DATABASE=' "$SITE_ROOT/.env" | head -1 | cut -d= -f2- | tr -d '"' )"
    DB_USER="$(grep -E '^DB_USERNAME=' "$SITE_ROOT/.env" | head -1 | cut -d= -f2- | tr -d '"' )"
    DB_PASS="$(grep -E '^DB_PASSWORD=' "$SITE_ROOT/.env" | head -1 | cut -d= -f2- | tr -d '"' )"
    DB_HOST="$(grep -E '^DB_HOST='     "$SITE_ROOT/.env" | head -1 | cut -d= -f2- | tr -d '"' )"
    DB_HOST="${DB_HOST:-127.0.0.1}"

    if [[ -z "$DB_NAME" ]]; then
        warn "No se pudo leer DB_DATABASE de .env, salto el backup"
    else
        if [[ $DRY_RUN -eq 1 ]]; then
            dry_say "mysqldump $DB_NAME → $BACKUP_FILE"
        else
            MYSQL_PWD="$DB_PASS" mysqldump --single-transaction --quick \
                -h "$DB_HOST" -u "$DB_USER" "$DB_NAME" 2>/dev/null \
                | gzip > "$BACKUP_FILE"
            ok "Backup en $BACKUP_FILE ($(du -h "$BACKUP_FILE" | cut -f1))"
        fi
    fi
else
    warn "Backup BD omitido (--skip-backup)"
fi

# ---------- Sincronización de archivos ------------------------------------- #
NEW_FILES_LIST=""
DELETED_FILES_LIST=""

if [[ $FULL_RSYNC -eq 0 && -n "$PREV_SHA" && "$PREV_SHA" != "$NEW_SHA" ]]; then
    say "Calculando archivos cambiados entre $PREV_SHA y $NEW_SHA"
    CHANGED_TMP="$(mktemp)"
    DELETED_TMP="$(mktemp)"
    (cd "$REPO_ROOT" && git diff --name-only --diff-filter=AM "$PREV_SHA" "$NEW_SHA") > "$CHANGED_TMP"
    (cd "$REPO_ROOT" && git diff --name-only --diff-filter=D  "$PREV_SHA" "$NEW_SHA") > "$DELETED_TMP"

    # Quitar ficheros vetados (.env, vendor, composer.*, etc.)
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

    CHANGED_COUNT="$(wc -l < "$CHANGED_TMP" | tr -d ' ')"
    DELETED_COUNT="$(wc -l < "$DELETED_TMP" | tr -d ' ')"
    say "Archivos a copiar: $CHANGED_COUNT  /  a borrar: $DELETED_COUNT"

    NEW_FILES_LIST="$CHANGED_TMP"
    DELETED_FILES_LIST="$DELETED_TMP"

    if [[ $CHANGED_COUNT -gt 0 ]]; then
        run "rsync -av ${EXCLUDES[*]} --files-from=$CHANGED_TMP $REPO_ROOT/ $SITE_ROOT/"
    fi

    if [[ $DELETED_COUNT -gt 0 ]]; then
        if [[ $DRY_RUN -eq 1 ]]; then
            while IFS= read -r f; do
                dry_say "rm $SITE_ROOT/$f"
            done < "$DELETED_TMP"
        else
            while IFS= read -r f; do
                [[ -n "$f" ]] && rm -f "$SITE_ROOT/$f"
            done < "$DELETED_TMP"
        fi
    fi
else
    say "Rsync completo (modo --full-rsync o primera vez)"
    run "rsync -av ${EXCLUDES[*]} $REPO_ROOT/ $SITE_ROOT/"
fi

# Para los seeders que tengan que correr y para `[skip ci]`-style commit msgs.
COMMIT_MSG="$(cd "$REPO_ROOT" && git log -1 --pretty=%B "$NEW_SHA" 2>/dev/null || echo "")"

# ---------- Autoload y caches ---------------------------------------------- #
say "Limpiando bootstrap cache"
run "rm -f $SITE_ROOT/bootstrap/cache/services.php $SITE_ROOT/bootstrap/cache/packages.php $SITE_ROOT/bootstrap/cache/config.php"

say "Regenerando autoload (composer dump-autoload)"
if should_sync_vendor; then
    run "cd $SITE_ROOT && $PHP_BIN $COMPOSER_BIN install --no-dev --no-interaction --prefer-dist --optimize-autoloader"
else
    run "cd $SITE_ROOT && $COMPOSER_BIN dump-autoload --no-scripts --optimize --no-interaction --ignore-platform-reqs"
fi

say "Re-descubriendo paquetes"
artisan "package:discover"

# ---------- Migraciones ---------------------------------------------------- #
if [[ $SKIP_MIGRATE -eq 0 ]]; then
    say "Aplicando migraciones"
    artisan "migrate --force"
else
    warn "Migraciones omitidas (--skip-migrate)"
fi

# ---------- Seeders production-safe ---------------------------------------- #
if [[ -n "$NEW_FILES_LIST" && -f "$NEW_FILES_LIST" ]]; then
    for seeder in "${PROD_SAFE_SEEDERS[@]}"; do
        if grep -qE "database/seeds?/${seeder}\.php$" "$NEW_FILES_LIST"; then
            say "Seeder nuevo detectado: $seeder"
            artisan "db:seed --class=$seeder --force"
        fi
    done
fi

# Comandos opcionales si han llegado al servidor
if [[ -n "$NEW_FILES_LIST" && -f "$NEW_FILES_LIST" ]]; then
    if grep -qE "app/Console/Commands/SeedDemosCommand\.php$" "$NEW_FILES_LIST"; then
        say "Comando webnu:seed-demos detectado, asegurando cartas demo"
        artisan "webnu:seed-demos || true"
    fi
fi

# ---------- Caches Laravel ------------------------------------------------- #
say "Refrescando cachés"
artisan "view:clear"
artisan "config:cache"
# route:cache se omite a propósito: si hay closures en routes/web.php fallaría.
# Tras el arreglo de Route::redirect en admin/integrations y admin/signage,
# puedes habilitar route:cache descomentando la línea siguiente.
# artisan "route:cache"

# ---------- Limpieza temporales -------------------------------------------- #
[[ -n "$NEW_FILES_LIST"     && -f "$NEW_FILES_LIST"     ]] && rm -f "$NEW_FILES_LIST"
[[ -n "$DELETED_FILES_LIST" && -f "$DELETED_FILES_LIST" ]] && rm -f "$DELETED_FILES_LIST"

# ---------- Cierre --------------------------------------------------------- #
if [[ $DRY_RUN -eq 0 ]]; then
    echo "$NEW_SHA" > "$STATE_FILE"
    log_line "DEPLOY $PREV_SHA → $NEW_SHA  msg=\"${COMMIT_MSG%%$'\n'*}\""
fi

if [[ $NO_DOWN -eq 0 ]]; then
    artisan "up"
fi
trap - EXIT

ok "Despliegue completo  ${PREV_SHA:0:7} → ${NEW_SHA:0:7}"
echo
echo "Verifica:"
echo "  https://webnu.es/carta/demo"
echo "  https://webnu.es/admin"
echo "  tail -n 50 $SITE_ROOT/storage/logs/laravel.log"
