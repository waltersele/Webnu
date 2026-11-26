#!/usr/bin/env bash
# Webnu — Script de despliegue desde GitHub al sitio live (producción o staging)
#
# Pensado para cPanel + SSH. Requiere PHP 8.3+ y funciona con Laravel 10 o superior:
# no hay versión de Laravel fija; tras `composer install` se comprueba que el major
# instalado en vendor/ coincide con el de composer.lock.
# Estructura asumida (producción):
#   $HOME/webnu-deploy/                ← clon git de origin/main
#   $HOME/public_html/webnu.es/        ← sitio live (la aplicación Laravel)
#
# Uso:
#   ./scripts/deploy.sh                # despliegue normal
#   ./scripts/deploy.sh --dry-run      # muestra qué cambiaría sin tocar nada
#   ./scripts/deploy.sh --rollback     # vuelve al SHA anterior (código + vendor/); NO restaura la BD
#   ./scripts/deploy.sh --skip-migrate # no ejecuta migraciones
#   ./scripts/deploy.sh --skip-backup  # no hace dump de la BD
#   ./scripts/deploy.sh --no-down      # no entra en mantenimiento (deploy en caliente)
#   ./scripts/deploy.sh --full-rsync   # rsync completo en lugar de delta git
#
# Lanzar desde la raíz del sitio:
#   cd $HOME/public_html/webnu.es && ./scripts/deploy.sh
#
# Variables de entorno (todas sobreescribibles; sirven para montar un staging):
#   SITE_ROOT    sitio live (def. $HOME/public_html/webnu.es)
#   REPO_ROOT    clon git de origen (def. $HOME/webnu-deploy)
#   REPO_BRANCH  rama a desplegar (def. main)
#   STATE_FILE   último SHA desplegado (def. $HOME/.webnu-last-deploy-sha); el SHA
#                anterior para --rollback se guarda en "$STATE_FILE.prev"
#   LOG_FILE     histórico de despliegues (def. $SITE_ROOT/storage/backups/deploys.log)
#   BACKUP_DIR   dumps de BD y snapshots vendor-<SHA>.tar.gz (def. $SITE_ROOT/storage/backups)
#   SMOKE_MENU_PATH  (opcional) carta pública para el smoke test, p. ej. /carta/demo
#   SMOKE_AUTH       (opcional) usuario:contraseña si el sitio tiene Directory Privacy (staging)
#   Ejemplo staging: ver docs/STAGING.md
#
# IMPORTANTE: rsync excluye scripts/, así que ESTE fichero NO se actualiza solo en el
# servidor. Tras cambiarlo en el repo, cópialo a mano:
#   cp $REPO_ROOT/scripts/deploy.sh $SITE_ROOT/scripts/deploy.sh && chmod +x $SITE_ROOT/scripts/deploy.sh
#
# Si el deploy falla una vez empezada la sincronización, el sitio se queda EN MANTENIMIENTO
# (no se reabre a medias): ejecuta --rollback y revisa el dump de BD indicado en pantalla.

set -euo pipefail

# ---------- Configuración --------------------------------------------------- #
SITE_ROOT="${SITE_ROOT:-$HOME/public_html/webnu.es}"
REPO_ROOT="${REPO_ROOT:-$HOME/webnu-deploy}"
REPO_REMOTE="${REPO_REMOTE:-origin}"
REPO_BRANCH="${REPO_BRANCH:-main}"
PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-}"
STATE_FILE="${STATE_FILE:-$HOME/.webnu-last-deploy-sha}"
LOG_FILE="${LOG_FILE:-$SITE_ROOT/storage/backups/deploys.log}"
BACKUP_DIR="${BACKUP_DIR:-$SITE_ROOT/storage/backups}"
SMOKE_MENU_PATH="${SMOKE_MENU_PATH:-}"   # p. ej. /carta/demo (opcional)
SMOKE_AUTH="${SMOKE_AUTH:-}"             # usuario:contraseña (staging con Directory Privacy)
VENDOR_SNAPSHOTS_KEEP=3                  # instantáneas vendor-<SHA>.tar.gz que se conservan

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
            awk 'NR>1 && /^set -/ {exit} NR>1 {print}' "$0"
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

php_version_of() {
    local bin="$1"
    "$bin" -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || echo '0.0'
}

php_is_modern() {
    case "$1" in
        8.[1-9]*) return 0 ;;
        8.[0-9][0-9]*) return 0 ;;
        *) return 1 ;;
    esac
}

resolve_php_bin() {
    if [[ -n "${PHP_BIN_OVERRIDE:-}" ]]; then
        PHP_BIN="$PHP_BIN_OVERRIDE"
        return
    fi
    if php_is_modern "$(php_version_of "$PHP_BIN")"; then
        return
    fi
    local candidate ver
    for candidate in \
        /opt/alt/php83/usr/bin/php \
        /opt/alt/php82/usr/bin/php \
        /opt/alt/php81/usr/bin/php \
        /usr/local/bin/php83 \
        /usr/local/bin/ea-php83
    do
        [[ -x "$candidate" ]] || continue
        ver="$(php_version_of "$candidate")"
        if php_is_modern "$ver"; then
            PHP_BIN="$candidate"
            return
        fi
    done
}

resolve_composer_invocation() {
    if [[ -n "$COMPOSER_BIN" ]]; then
        return
    fi
    if command -v composer >/dev/null 2>&1; then
        COMPOSER_BIN="$(command -v composer)"
        return
    fi
    local phar
    for phar in \
        "$SITE_ROOT/composer.phar" \
        "$REPO_ROOT/composer.phar" \
        "$HOME/composer.phar" \
        /usr/local/bin/composer.phar
    do
        if [[ -f "$phar" ]]; then
            COMPOSER_BIN="$PHP_BIN $phar"
            return
        fi
    done
    for phar in /usr/local/bin/composer /opt/cpanel/composer/bin/composer; do
        if [[ -x "$phar" ]]; then
            COMPOSER_BIN="$phar"
            return
        fi
    done
    die "Composer no encontrado. Define COMPOSER_BIN (p. ej. export COMPOSER_BIN=\"$PHP_BIN ~/composer.phar\")"
}

composer_run() {
    run "cd $SITE_ROOT && $COMPOSER_BIN $*"
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

live_laravel_version() {
    local f="$SITE_ROOT/vendor/laravel/framework/src/Illuminate/Foundation/Application.php"
    if [[ ! -f "$f" ]]; then
        echo ""
        return
    fi
    grep -E "const VERSION = '" "$f" | head -1 | sed -E "s/.*'([^']+)'.*/\1/" || echo ""
}

# Major (primer número) de una versión: "13.44.1" → 13, "v10.48.0" → 10
major_of() {
    local v="${1#v}"
    echo "${v%%.*}"
}

# Major de laravel/framework según un composer.lock (vacío si no se puede leer).
# Usa PHP (json_decode); no depende de jq.
lock_laravel_major() {
    local lock="$1" major=""
    [[ -f "$lock" ]] || { echo ""; return 0; }
    major="$("$PHP_BIN" -r '
        $l = json_decode((string) @file_get_contents($argv[1]), true);
        foreach (($l["packages"] ?? []) as $p) {
            if (($p["name"] ?? "") === "laravel/framework") {
                echo explode(".", ltrim((string) ($p["version"] ?? ""), "vV"))[0];
                break;
            }
        }
    ' "$lock" 2>/dev/null || true)"
    [[ "$major" =~ ^[0-9]+$ ]] || major=""
    echo "$major"
}

# return 0 = sí hay upgrade mayor (forzar full-rsync + composer install)
# Compara el major instalado en el sitio con el del composer.lock del repo.
detect_major_upgrade() {
    local live_major repo_major
    live_major="$(major_of "$(live_laravel_version)")"
    repo_major="$(lock_laravel_major "$REPO_ROOT/composer.lock")"
    if [[ -z "$live_major" || -z "$repo_major" || "$live_major" != "$repo_major" ]]; then
        return 0
    fi
    local lock_live="$SITE_ROOT/composer.lock"
    local lock_repo="$REPO_ROOT/composer.lock"
    if [[ ! -f "$lock_live" || ! -f "$lock_repo" ]]; then
        return 0
    fi
    if ! cmp -s "$lock_live" "$lock_repo"; then
        return 0
    fi
    return 1
}

# El major de Laravel instalado en vendor/ debe coincidir con el de composer.lock del sitio.
assert_laravel_matches_lock() {
    local ver inst_major lock_major
    ver="$(live_laravel_version)"
    inst_major="$(major_of "$ver")"
    lock_major="$(lock_laravel_major "$SITE_ROOT/composer.lock")"
    [[ -n "$lock_major" ]] || die "No se pudo leer laravel/framework de $SITE_ROOT/composer.lock. Aborto sin marcar SHA."
    [[ -n "$inst_major" ]] || die "No se detecta Laravel en $SITE_ROOT/vendor (VERSION desconocida). Aborto sin marcar SHA."
    if [[ "$inst_major" != "$lock_major" ]]; then
        die "vendor/ tiene Laravel $ver pero composer.lock pide ${lock_major}.x. Aborto sin marcar SHA."
    fi
    ok "Laravel VERSION=$ver (major $inst_major coincide con composer.lock)"
}

# Instantánea de vendor/ + composer.json/lock del sitio: $BACKUP_DIR/vendor-<SHA>.tar.gz
# Si ya existe una para ese SHA se conserva (un reintento tras un deploy fallido
# capturaría un vendor a medias). Se mantienen las $VENDOR_SNAPSHOTS_KEEP más recientes.
snapshot_vendor() {
    local sha="${1:-}" snap tmp old
    local -a files=()
    if [[ -z "$sha" ]]; then
        warn "Sin SHA previo: no se puede nombrar la instantánea de vendor/."
        return 0
    fi
    snap="$BACKUP_DIR/vendor-$sha.tar.gz"
    if [[ ! -d "$SITE_ROOT/vendor" ]]; then
        warn "No hay vendor/ en $SITE_ROOT; instantánea omitida."
        return 0
    fi
    if [[ $DRY_RUN -eq 1 ]]; then
        dry_say "tar vendor/ composer.json composer.lock → $snap (se conservan las $VENDOR_SNAPSHOTS_KEEP más recientes)"
        return 0
    fi
    mkdir -p "$BACKUP_DIR"
    if [[ -f "$snap" ]]; then
        touch "$snap"
        ok "Ya existe instantánea de vendor/ para ${sha:0:7}; se conserva: $snap"
    else
        files=(vendor)
        [[ -f "$SITE_ROOT/composer.json" ]] && files+=(composer.json)
        [[ -f "$SITE_ROOT/composer.lock" ]] && files+=(composer.lock)
        tmp="$snap.tmp"
        if ! tar -czf "$tmp" -C "$SITE_ROOT" "${files[@]}"; then
            rm -f "$tmp"
            die "No se pudo crear la instantánea de vendor/ ($snap). No se ha tocado nada."
        fi
        mv -f "$tmp" "$snap"
        ok "Instantánea de vendor/ en $snap ($(du -h "$snap" | cut -f1))"
    fi
    while IFS= read -r old; do
        [[ -n "$old" ]] && rm -f "$old"
    done < <(ls -1t "$BACKUP_DIR"/vendor-*.tar.gz 2>/dev/null | tail -n +"$((VENDOR_SNAPSHOTS_KEEP + 1))")
    return 0
}

# Sustituye vendor/, composer.json y composer.lock del sitio por los de una instantánea.
restore_vendor_snapshot() {
    local snap="$1" tmp="$SITE_ROOT/.vendor-restore-$$" f
    if [[ $DRY_RUN -eq 1 ]]; then
        dry_say "restaurar vendor/ composer.json composer.lock desde $snap"
        return 0
    fi
    rm -rf "$tmp"
    mkdir -p "$tmp"
    if ! tar -xzf "$snap" -C "$tmp"; then
        rm -rf "$tmp"
        die "No se pudo extraer $snap"
    fi
    if [[ ! -f "$tmp/vendor/autoload.php" ]]; then
        rm -rf "$tmp"
        die "La instantánea $snap no contiene vendor/autoload.php; no la aplico."
    fi
    rm -rf "$SITE_ROOT/vendor.rollback-old"
    if [[ -d "$SITE_ROOT/vendor" ]]; then
        mv "$SITE_ROOT/vendor" "$SITE_ROOT/vendor.rollback-old"
    fi
    mv "$tmp/vendor" "$SITE_ROOT/vendor"
    for f in composer.json composer.lock; do
        if [[ -f "$tmp/$f" ]]; then
            cp -f "$tmp/$f" "$SITE_ROOT/$f"
        fi
    done
    rm -rf "$tmp" "$SITE_ROOT/vendor.rollback-old"
    ok "vendor/, composer.json y composer.lock restaurados desde $snap"
}

# Smoke test HTTP contra APP_URL (.env del sitio). return 0 = todo 200.
smoke_test() {
    local app_url p url code fails=0
    local -a auth=()
    local -a paths=(/ /login /register)
    app_url="$(grep -E '^APP_URL=' "$SITE_ROOT/.env" 2>/dev/null | head -1 | cut -d= -f2- | tr -d "\"'\r" || true)"
    app_url="${app_url%/}"
    if [[ -z "$app_url" ]]; then
        warn "No se pudo leer APP_URL de $SITE_ROOT/.env; no se puede hacer el smoke test."
        return 1
    fi
    if ! command -v curl >/dev/null 2>&1; then
        warn "curl no está instalado; no se puede hacer el smoke test."
        return 1
    fi
    [[ -n "$SMOKE_MENU_PATH" ]] && paths+=("$SMOKE_MENU_PATH")
    [[ -n "$SMOKE_AUTH" ]] && auth=(-u "$SMOKE_AUTH")
    say "Smoke test contra $app_url"
    for p in "${paths[@]}"; do
        [[ "$p" == /* ]] || p="/$p"
        url="$app_url$p"
        code="$(curl -s -o /dev/null -L --max-time 20 -w '%{http_code}' ${auth[@]+"${auth[@]}"} "$url" 2>/dev/null || true)"
        code="${code:-000}"
        if [[ "$code" = "200" ]]; then
            ok "$code  $url"
        else
            printf "${C_RED}✗${C_RESET} %s  %s\n" "$code" "$url" >&2
            fails=$((fails + 1))
        fi
    done
    [[ $fails -eq 0 ]]
}

# ---------- Estado y trap de salida ---------------------------------------- #
EXIT_MODE="deploy"      # deploy | rollback
DEPLOY_TOUCHED=0        # 1 = ya se empezó a modificar el sitio (rsync/composer)
DEPLOY_DONE=0           # 1 = deploy terminado y SHA marcado (solo falta `up`)
MAINTENANCE=0
BACKUP_FILE=""

on_exit() {
    local rc=$?
    trap - EXIT
    [[ $rc -eq 0 ]] && exit 0
    # En dry-run nunca se toca nada.
    [[ $DRY_RUN -eq 1 ]] && exit "$rc"

    if [[ $DEPLOY_DONE -eq 1 ]]; then
        printf "\n${C_RED}✗ El deploy terminó pero no se pudo reabrir el sitio.${C_RESET}\n" >&2
        echo "  Ejecuta a mano: $PHP_BIN $SITE_ROOT/artisan up" >&2
        exit "$rc"
    fi

    if [[ $DEPLOY_TOUCHED -eq 0 ]]; then
        # Fallo antes de tocar el sitio (deploy o rollback): es seguro reabrirlo.
        if [[ $MAINTENANCE -eq 1 ]]; then
            "$PHP_BIN" "$SITE_ROOT/artisan" up >/dev/null 2>&1 || true
            warn "Fallo antes de modificar el sitio; se ha reabierto (artisan up). No se ha tocado nada."
        fi
        exit "$rc"
    fi

    # Fallo con el sitio a medias: NO se reabre.
    {
        printf "\n${C_RED}════════════════════════════════════════════════════════════${C_RESET}\n"
        if [[ "$EXIT_MODE" = "rollback" ]]; then
            printf "${C_RED}✗ EL ROLLBACK HA FALLADO (exit %s)${C_RESET}\n" "$rc"
        else
            printf "${C_RED}✗ EL DEPLOY HA FALLADO A MEDIAS (exit %s)${C_RESET}\n" "$rc"
        fi
        if [[ $MAINTENANCE -eq 1 ]]; then
            echo "  El sitio SE QUEDA EN MANTENIMIENTO (no se reabre a medias)."
        else
            echo "  OJO: el sitio NO está en mantenimiento (--no-down o 'down' falló) y puede estar en un estado inconsistente."
        fi
        echo "  Qué hacer:"
        if [[ "$EXIT_MODE" = "rollback" ]]; then
            echo "   1) Revisa el error de arriba y vuelve a lanzar: ./scripts/deploy.sh --rollback"
            echo "   2) Si hay instantánea: $BACKUP_DIR/vendor-<SHA>.tar.gz (vendor/, composer.json, composer.lock)"
        else
            echo "   1) Vuelve atrás: ./scripts/deploy.sh --rollback   (código + vendor/ del SHA anterior)"
            echo "   2) Reabre solo si lo has revisado: $PHP_BIN $SITE_ROOT/artisan up"
        fi
        if [[ -n "$BACKUP_FILE" && -f "$BACKUP_FILE" ]]; then
            echo "   BD: backup de este deploy en $BACKUP_FILE"
        else
            echo "   BD: sin backup de este deploy; últimos dumps en $BACKUP_DIR/db-*.sql.gz"
        fi
        echo "   El rollback NO restaura la BD. Si se aplicaron migraciones, restaura el dump a mano:"
        echo "     gunzip -c <dump>.sql.gz | mysql -u USER -p DB_NAME"
        printf "${C_RED}════════════════════════════════════════════════════════════${C_RESET}\n"
    } >&2
    exit "$rc"
}
trap on_exit EXIT
trap 'exit 130' INT
trap 'exit 143' TERM

# ---------- Comprobaciones previas ----------------------------------------- #
[[ -d "$SITE_ROOT" ]]  || die "No existe SITE_ROOT: $SITE_ROOT"
[[ -d "$REPO_ROOT" ]]  || die "No existe REPO_ROOT: $REPO_ROOT (clona el repo en \$HOME/webnu-deploy)"
[[ -d "$REPO_ROOT/.git" ]] || die "$REPO_ROOT no es un repositorio git"
command -v rsync >/dev/null      || die "rsync no está instalado"

resolve_php_bin
say "PHP: $PHP_BIN ($("$PHP_BIN" -r 'echo PHP_VERSION;' 2>/dev/null || echo desconocido))"
resolve_composer_invocation
say "Composer: $COMPOSER_BIN"

command -v "$PHP_BIN" >/dev/null 2>&1 || [[ -x "$PHP_BIN" ]] || die "$PHP_BIN no está disponible"

# ---------- ROLLBACK ------------------------------------------------------- #
if [[ $ROLLBACK -eq 1 ]]; then
    EXIT_MODE="rollback"
    cd "$REPO_ROOT"
    [[ $DRY_RUN -eq 1 ]] || git fetch --quiet "$REPO_REMOTE" || warn "git fetch falló; se usa el repo local"
    CURRENT="$(git rev-parse HEAD)"
    # Destino: ROLLBACK_TO explícito; si el último deploy falló (STATE_FILE ≠ HEAD del repo)
    # el último bueno es STATE_FILE; si terminó bien, el SHA anterior guardado en STATE_FILE.prev.
    PREV="${ROLLBACK_TO:-}"
    STATE_SHA=""
    [[ -f "$STATE_FILE" ]] && STATE_SHA="$(git rev-parse --verify "$(tr -d '[:space:]' < "$STATE_FILE")^{commit}" 2>/dev/null || true)"
    if [[ -z "$PREV" && -n "$STATE_SHA" && "$STATE_SHA" != "$CURRENT" ]]; then
        PREV="$STATE_SHA"
    fi
    if [[ -z "$PREV" && -f "$STATE_FILE.prev" ]]; then
        PREV="$(tr -d '[:space:]' < "$STATE_FILE.prev")"
    fi
    [[ -n "$PREV" ]] || die "No hay SHA anterior registrado ($STATE_FILE.prev); nada que rollbackear. Indica uno con: ROLLBACK_TO=<sha> ./scripts/deploy.sh --rollback"
    PREV="$(git rev-parse --verify "${PREV}^{commit}" 2>/dev/null)" || die "El SHA anterior no existe en $REPO_ROOT"
    say "Rollback al SHA $PREV"
    if [[ "$CURRENT" = "$PREV" ]]; then
        warn "El repo ya está en $PREV; ¿buscas un rollback más antiguo? Hazlo a mano."
        exit 0
    fi
    SNAP="$BACKUP_DIR/vendor-$PREV.tar.gz"

    printf "\n${C_YELLOW}!! ATENCIÓN: el rollback NO restaura la base de datos.${C_RESET}\n"
    echo "   Las migraciones nuevas NO se revierten solas: el código antiguo correrá contra el esquema nuevo."
    LAST_DUMP="$(ls -1t "$BACKUP_DIR"/db-*.sql.gz 2>/dev/null | head -1 || true)"
    echo "   Último dump de BD: ${LAST_DUMP:-ninguno en $BACKUP_DIR}"
    echo

    if [[ $NO_DOWN -eq 0 ]]; then
        if [[ $DRY_RUN -eq 1 ]]; then
            dry_say "$PHP_BIN $SITE_ROOT/artisan down ..."
            MAINTENANCE=1
        else
            set +e
            "$PHP_BIN" "$SITE_ROOT/artisan" down --retry=15
            DOWN_STATUS=$?
            set -e
            if [[ $DOWN_STATUS -eq 0 ]]; then
                MAINTENANCE=1
            else
                warn "artisan down falló (exit $DOWN_STATUS). Continuando."
            fi
        fi
    fi

    # Desde aquí se modifica el sitio: un fallo lo deja en mantenimiento.
    [[ $DRY_RUN -eq 1 ]] || DEPLOY_TOUCHED=1
    run "cd $REPO_ROOT && git reset --hard $PREV"
    run "rsync -av ${EXCLUDES[*]} $REPO_ROOT/ $SITE_ROOT/"
    run "rm -f $SITE_ROOT/bootstrap/cache/services.php $SITE_ROOT/bootstrap/cache/packages.php $SITE_ROOT/bootstrap/cache/config.php"

    if [[ -f "$SNAP" ]]; then
        say "Restaurando vendor/ desde la instantánea $SNAP"
        restore_vendor_snapshot "$SNAP"
    else
        warn "No existe $SNAP; se reinstala vendor/ con composer install usando el composer.lock de ${PREV:0:7} (necesita red)."
        composer_run "install --no-dev --prefer-dist --optimize-autoloader --no-interaction"
    fi
    if [[ $DRY_RUN -eq 0 ]]; then
        assert_laravel_matches_lock
    fi

    artisan "package:discover"
    artisan "view:clear"
    artisan "config:cache"

    if [[ $DRY_RUN -eq 0 ]]; then
        echo "$PREV" > "$STATE_FILE"
        rm -f "$STATE_FILE.prev"
        log_line "ROLLBACK a $PREV (desde $CURRENT)"
    fi
    if [[ $MAINTENANCE -eq 1 ]]; then
        artisan "up"
    fi
    trap - EXIT
    ok "Rollback completo a ${PREV:0:7} (código + vendor/)"
    warn "La BD NO se ha tocado: si el deploy fallido aplicó migraciones, restaura el dump (ver docs/deploy.md)."
    warn "El repo $REPO_ROOT queda en ${PREV:0:7}; el próximo deploy volverá a $REPO_REMOTE/$REPO_BRANCH."
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

if detect_major_upgrade; then
    warn "Upgrade mayor detectado (major de Laravel live ≠ el del repo y/o composer.lock distinto). Forzando --full-rsync --with-vendor."
    FULL_RSYNC=1
    WITH_VENDOR=1
fi

# En Laravel 10+ necesitamos composer.json/lock y vendor.
if should_sync_vendor; then
    # Quitamos exclusiones heredadas.
    EXCLUDES=(${EXCLUDES[@]/--exclude='composer.json'/})
    EXCLUDES=(${EXCLUDES[@]/--exclude='composer.lock'/})
    EXCLUDES=(${EXCLUDES[@]/--exclude='vendor/'/})
fi

# ---------- Mantenimiento --------------------------------------------------- #
if [[ $NO_DOWN -eq 0 ]]; then
    say "Intentando modo mantenimiento"
    if [[ $DRY_RUN -eq 1 ]]; then
        dry_say "$PHP_BIN $SITE_ROOT/artisan down ..."
        MAINTENANCE=1
    else
        set +e
        "$PHP_BIN" "$SITE_ROOT/artisan" down --retry=30
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

# El trap on_exit (arriba) decide al fallar: antes del rsync reabre el sitio (no se ha
# tocado nada); después lo deja en mantenimiento e imprime cómo recuperarlo.

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
        die "No se pudo leer DB_DATABASE de .env; no despliego sin backup. Usa --skip-backup si es intencional."
    else
        if [[ $DRY_RUN -eq 1 ]]; then
            dry_say "mysqldump $DB_NAME → $BACKUP_FILE"
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
    fi
else
    warn "Backup BD omitido (--skip-backup)"
fi

# ---------- Instantánea de vendor/ (para --rollback) ----------------------- #
say "Instantánea de vendor/ y composer.* (SHA previo ${PREV_SHA:0:7})"
snapshot_vendor "$PREV_SHA"

# A partir de aquí se modifica el sitio: un fallo ya no reabre el sitio.
[[ $DRY_RUN -eq 1 ]] || DEPLOY_TOUCHED=1

# ---------- Sincronización de archivos ------------------------------------- #
NEW_FILES_LIST=""
DELETED_FILES_LIST=""

if [[ $FULL_RSYNC -eq 0 && -n "$PREV_SHA" && "$PREV_SHA" != "$NEW_SHA" ]]; then
    say "Calculando archivos cambiados entre $PREV_SHA y $NEW_SHA"
    CHANGED_TMP="$(mktemp)"
    DELETED_TMP="$(mktemp)"
    (cd "$REPO_ROOT" && git diff --name-only --diff-filter=AM "$PREV_SHA" "$NEW_SHA") > "$CHANGED_TMP"
    (cd "$REPO_ROOT" && git diff --name-only --diff-filter=D  "$PREV_SHA" "$NEW_SHA") > "$DELETED_TMP"

    # Quitar ficheros vetados (.env, vendor, etc.). En stack moderno sí sincronizamos composer.json/lock.
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

if should_sync_vendor; then
    say "Instalando dependencias (composer install --no-dev)"
    composer_run "install --no-dev --no-interaction --prefer-dist --optimize-autoloader"
    if [[ $DRY_RUN -eq 0 ]]; then
        assert_laravel_matches_lock
    fi
else
    say "Regenerando autoload (composer dump-autoload)"
    composer_run "dump-autoload --no-scripts --optimize --no-interaction --ignore-platform-reqs"
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

say "Comprobando salud del despliegue"
artisan "webnu:deploy-check"

# ---------- Limpieza temporales -------------------------------------------- #
[[ -n "$NEW_FILES_LIST"     && -f "$NEW_FILES_LIST"     ]] && rm -f "$NEW_FILES_LIST"
[[ -n "$DELETED_FILES_LIST" && -f "$DELETED_FILES_LIST" ]] && rm -f "$DELETED_FILES_LIST"

# ---------- Cierre --------------------------------------------------------- #
if [[ $DRY_RUN -eq 0 ]]; then
    echo "$NEW_SHA" > "$STATE_FILE"
    if [[ -n "$PREV_SHA" && "$PREV_SHA" != "$NEW_SHA" ]]; then
        echo "$PREV_SHA" > "$STATE_FILE.prev"
    fi
    log_line "DEPLOY $PREV_SHA → $NEW_SHA  msg=\"${COMMIT_MSG%%$'\n'*}\""
fi

DEPLOY_DONE=1
if [[ $MAINTENANCE -eq 1 ]]; then
    artisan "up"
fi
trap - EXIT

ok "Despliegue completo  ${PREV_SHA:0:7} → ${NEW_SHA:0:7}"
echo

# ---------- Smoke test ------------------------------------------------------ #
SMOKE_OK=1
if [[ $DRY_RUN -eq 1 ]]; then
    dry_say "smoke test curl a APP_URL: / /login /register ${SMOKE_MENU_PATH}"
elif ! smoke_test; then
    SMOKE_OK=0
fi

echo "Verifica:"
echo "  tail -n 50 $SITE_ROOT/storage/logs/laravel.log"

if [[ $SMOKE_OK -eq 0 ]]; then
    {
        printf "\n${C_RED}════════════════════════════════════════════════════════════${C_RESET}\n"
        printf "${C_RED}✗ SMOKE TEST FALLIDO: el deploy se aplicó, pero alguna URL no responde 200.${C_RESET}\n"
        echo "  El sitio está ARRIBA con el código nuevo. NO se ha hecho rollback automático."
        echo "  Revisa: tail -n 80 $SITE_ROOT/storage/logs/laravel.log"
        echo "  Si es grave: ./scripts/deploy.sh --rollback   (no restaura la BD)"
        printf "${C_RED}════════════════════════════════════════════════════════════${C_RESET}\n"
    } >&2
    exit 3
fi
