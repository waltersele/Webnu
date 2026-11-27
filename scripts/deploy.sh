#!/usr/bin/env bash
# Webnu — Script de despliegue desde GitHub al sitio live (producción o staging)
#
# Pensado para cPanel + SSH. Requiere PHP 8.3+ y funciona con Laravel 10 o superior:
# no hay versión de Laravel fija; se comprueba que el major instalado en vendor/
# coincide con el de composer.lock.
# Estructura asumida (producción):
#   $HOME/webnu-deploy/                ← clon git de origin/main
#   $HOME/public_html/webnu.es/        ← sitio live (la aplicación Laravel)
#
# Uso (ejecútalo dentro de `screen` o `tmux`: si se corta el SSH a mitad el sitio
# quedaría en mantenimiento):
#   ./scripts/deploy.sh                # despliegue normal
#   ./scripts/deploy.sh --dry-run      # muestra qué cambiaría; solo hace git fetch (no escribe en el sitio)
#   ./scripts/deploy.sh --rollback     # vuelve al SHA anterior (código + vendor/); NO restaura la BD
#   ./scripts/deploy.sh --skip-migrate # no ejecuta migraciones
#   ./scripts/deploy.sh --skip-backup  # no hace dump de la BD
#   ./scripts/deploy.sh --no-down      # no entra en mantenimiento (deploy en caliente)
#   ./scripts/deploy.sh --full-rsync   # rsync completo en lugar de delta git
#   ./scripts/deploy.sh --yes          # no pide confirmación (host de APP_URL) aunque haya TTY
#
# El SITE_ROOT es, por defecto, la carpeta que contiene scripts/ (el propio sitio).
#
# Configuración por sitio: $SITE_ROOT/.deploy.env (solo líneas CLAVE=valor con claves permitidas:
# REPO_ROOT REPO_BRANCH REPO_REMOTE STATE_FILE LOG_FILE BACKUP_DIR PHP_BIN COMPOSER_BIN
# SMOKE_MENU_PATH SMOKE_AUTH; no se ejecuta con source; se excluye del rsync). Ejemplo staging:
#   REPO_ROOT=$HOME/webnu-staging
#   REPO_BRANCH=l13/paso-3-laravel-13
#   STATE_FILE=$HOME/.webnu-staging-last-deploy-sha
#   SMOKE_AUTH=usuario:clave          # si hay Directory Privacy (chmod 600 .deploy.env)
#   SMOKE_MENU_PATH=/carta/demo
# Las variables del entorno tienen prioridad sobre .deploy.env.
#
# Variables (sobreescribibles):
#   SITE_ROOT    NO exportarlo: es siempre la carpeta padre de scripts/ (si el entorno trae otro, aborta)
#   REPO_ROOT    clon git de origen (def. $HOME/webnu-deploy)
#   REPO_BRANCH  rama a desplegar (def. main)
#   DEPLOY_REF   SHA o tag exacto a desplegar en lugar de $REPO_REMOTE/$REPO_BRANCH
#   ROLLBACK_TO  SHA destino explícito para --rollback (máxima prioridad)
#   STATE_FILE   último SHA desplegado (def. $HOME/.webnu-last-deploy-sha). Junto a él:
#                .prev (SHA anterior), .pending (deploy en curso/fallido), .rollback-target, .lock
#   LOG_FILE     histórico (def. $SITE_ROOT/storage/backups/deploys.log)
#   BACKUP_DIR   dumps de BD, snapshots vendor-<SHA>.tar.gz y deploy-<fecha>.out
#   SMOKE_MENU_PATH, SMOKE_AUTH   ver arriba
#   SKIP_SCRIPT_CHECK=1           no exigir que este script coincida con el del destino (en producción: solo con TTY)
#   ALLOW_CUSTOM=1                permitir en producción un REPO_ROOT/STATE_FILE/rama no estándar
#
# Seguridad: si SITE_ROOT no es $HOME/public_html/webnu.es y STATE_FILE o REPO_ROOT siguen en
# los valores de producción, el script ABORTA (evita que un comando de staging toque producción).
#
# IMPORTANTE: rsync excluye scripts/, así que ESTE fichero NO se actualiza solo en el
# servidor; el script aborta si difiere del del commit destino. Actualízalo a mano (el clon aún no está en ese commit):
#   git -C $REPO_ROOT show <SHA-destino>:scripts/deploy.sh > $SITE_ROOT/scripts/deploy.sh && chmod +x $SITE_ROOT/scripts/deploy.sh
#
# Si el deploy falla una vez empezada la sincronización, el sitio se queda EN MANTENIMIENTO
# (no se reabre a medias): ejecuta --rollback y revisa el dump de BD indicado en pantalla.

set -euo pipefail

SCRIPT_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd -P)/$(basename "${BASH_SOURCE[0]}")"
SCRIPT_DIR="$(dirname "$SCRIPT_PATH")"

# ---------- Helpers básicos ------------------------------------------------- #
C_RESET=$'\e[0m'; C_BLUE=$'\e[34m'; C_GREEN=$'\e[32m'; C_YELLOW=$'\e[33m'; C_RED=$'\e[31m'
say()  { printf "${C_BLUE}→${C_RESET} %s\n" "$*"; }
ok()   { printf "${C_GREEN}✓${C_RESET} %s\n" "$*"; }
warn() { printf "${C_YELLOW}!${C_RESET} %s\n" "$*"; }
die()  { printf "${C_RED}✗${C_RESET} %s\n" "$*" >&2; exit 1; }

canon() { ( cd "$1" 2>/dev/null && pwd -P ) || echo "$1"; }

# ---------- Flags ----------------------------------------------------------- #
DRY_RUN=0
ROLLBACK=0
SKIP_MIGRATE=0
SKIP_BACKUP=0
NO_DOWN=0
FULL_RSYNC=0
WITH_VENDOR=0
ASSUME_YES=0

while [[ $# -gt 0 ]]; do
    case "$1" in
        --dry-run)      DRY_RUN=1 ;;
        --rollback)     ROLLBACK=1 ;;
        --skip-migrate) SKIP_MIGRATE=1 ;;
        --skip-backup)  SKIP_BACKUP=1 ;;
        --no-down)      NO_DOWN=1 ;;
        --full-rsync)   FULL_RSYNC=1 ;;
        --with-vendor)  WITH_VENDOR=1 ;;
        --yes|-y)       ASSUME_YES=1 ;;
        -h|--help)
            awk 'NR>1 && /^set -/ {exit} NR>1 {print}' "$SCRIPT_PATH"
            exit 0
            ;;
        *)
            echo "Opción desconocida: $1" >&2
            exit 2
            ;;
    esac
    shift
done

# ---------- Configuración --------------------------------------------------- #
PROD_SITE_ROOT="$HOME/public_html/webnu.es"
PROD_REPO_ROOT="$HOME/webnu-deploy"
PROD_STATE_FILE="$HOME/.webnu-last-deploy-sha"

# Avisa de variables de deploy exportadas en el entorno (típico error: sobras de staging).
_envset=()
for _v in SITE_ROOT REPO_ROOT REPO_REMOTE REPO_BRANCH DEPLOY_REF STATE_FILE LOG_FILE BACKUP_DIR \
          PHP_BIN COMPOSER_BIN SMOKE_MENU_PATH SMOKE_AUTH SKIP_SCRIPT_CHECK ROLLBACK_TO ALLOW_CUSTOM; do
    if [[ -n "${!_v+x}" ]]; then
        if [[ "$_v" == "SMOKE_AUTH" ]]; then _envset+=("$_v=***"); else _envset+=("$_v=${!_v}"); fi
    fi
done
if [[ ${#_envset[@]} -gt 0 ]]; then
    warn "Variables de deploy presentes en el entorno (mandan sobre .deploy.env): ${_envset[*]}"
fi

# SITE_ROOT = carpeta que contiene scripts/. Un SITE_ROOT del entorno que no coincida, aborta.
SITE_FROM_SCRIPT="$(cd "$SCRIPT_DIR/.." && pwd -P)"
if [[ -n "${SITE_ROOT:-}" && "$(canon "$SITE_ROOT")" != "$SITE_FROM_SCRIPT" ]]; then
    die "SITE_ROOT=$SITE_ROOT (entorno) no coincide con la carpeta de este script ($SITE_FROM_SCRIPT). Lanza el script de DENTRO del sitio que quieres desplegar y no exportes SITE_ROOT. Aborto sin tocar nada."
fi
SITE_ROOT="$SITE_FROM_SCRIPT"

# .deploy.env: NO se ejecuta con source. Solo líneas CLAVE=valor con claves de la lista blanca.
# $HOME y ~ se expanden; cualquier otro $ o ` se rechaza (usa comillas simples para valores literales).
DEPLOY_ENV_ALLOWED=" REPO_ROOT REPO_BRANCH REPO_REMOTE STATE_FILE LOG_FILE BACKUP_DIR PHP_BIN COMPOSER_BIN SMOKE_MENU_PATH SMOKE_AUTH "
load_deploy_env() {
    local file="$1" line key val n=0 literal
    while IFS= read -r line || [[ -n "$line" ]]; do
        n=$((n + 1))
        line="${line%$'\r'}"
        [[ -z "${line//[[:space:]]/}" || "$line" =~ ^[[:space:]]*# ]] && continue
        if [[ "$line" =~ ^[[:space:]]*([A-Za-z_][A-Za-z0-9_]*)=(.*)$ ]]; then
            key="${BASH_REMATCH[1]}"; val="${BASH_REMATCH[2]}"
        else
            warn ".deploy.env:$n ignorada (no es CLAVE=valor)"
            continue
        fi
        if [[ "$DEPLOY_ENV_ALLOWED" != *" $key "* ]]; then
            warn ".deploy.env:$n clave no permitida '$key': ignorada (solo ${DEPLOY_ENV_ALLOWED})"
            continue
        fi
        literal=0
        if [[ "$val" =~ ^\'(.*)\'$ ]]; then
            val="${BASH_REMATCH[1]}"; literal=1
        elif [[ "$val" =~ ^\"(.*)\"$ ]]; then
            val="${BASH_REMATCH[1]}"
        fi
        if [[ $literal -eq 0 ]]; then
            val="${val//\$\{HOME\}/$HOME}"
            val="${val//\$HOME/$HOME}"
            [[ "$val" == "~/"* ]] && val="$HOME/${val:2}"
            if [[ "$val" == *'$'* || "$val" == *'`'* ]]; then
                warn ".deploy.env:$n '$key' contiene una expansión no permitida: ignorada"
                continue
            fi
        fi
        # El entorno manda sobre el fichero.
        if [[ -n "${!key+x}" ]]; then continue; fi
        printf -v "$key" '%s' "$val"
    done < "$file"
}
if [[ -f "$SITE_ROOT/.deploy.env" ]]; then
    load_deploy_env "$SITE_ROOT/.deploy.env"
    DEPLOY_ENV_LOADED=1
else
    DEPLOY_ENV_LOADED=0
fi

REPO_ROOT="${REPO_ROOT:-$PROD_REPO_ROOT}"
REPO_REMOTE="${REPO_REMOTE:-origin}"
REPO_BRANCH="${REPO_BRANCH:-main}"
DEPLOY_REF="${DEPLOY_REF:-}"
PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-}"
STATE_FILE="${STATE_FILE:-$PROD_STATE_FILE}"
LOG_FILE="${LOG_FILE:-$SITE_ROOT/storage/backups/deploys.log}"
BACKUP_DIR="${BACKUP_DIR:-$SITE_ROOT/storage/backups}"
SMOKE_MENU_PATH="${SMOKE_MENU_PATH:-}"   # p. ej. /carta/demo (opcional)
SMOKE_AUTH="${SMOKE_AUTH:-}"             # usuario:contraseña (staging con Directory Privacy)
ROLLBACK_TO="${ROLLBACK_TO:-}"
ALLOW_CUSTOM="${ALLOW_CUSTOM:-0}"
VENDOR_SNAPSHOTS_KEEP=3                  # instantáneas vendor-<SHA>.tar.gz que se conservan

# Freno anti-accidentes en AMBOS sentidos.
IS_PROD_SITE=0
[[ "$SITE_ROOT" == "$(canon "$PROD_SITE_ROOT")" ]] && IS_PROD_SITE=1
if [[ $IS_PROD_SITE -eq 0 ]]; then
    # Un sitio que no es producción no puede usar el estado/repo de producción.
    if [[ "$STATE_FILE" == "$PROD_STATE_FILE" || "$(canon "$REPO_ROOT")" == "$(canon "$PROD_REPO_ROOT")" ]]; then
        die "SITE_ROOT=$SITE_ROOT no es producción ($PROD_SITE_ROOT) pero STATE_FILE o REPO_ROOT apuntan a los de PRODUCCIÓN. Crea $SITE_ROOT/.deploy.env con REPO_ROOT, REPO_BRANCH y STATE_FILE propios (ver docs/STAGING.md). Aborto sin tocar nada."
    fi
else
    # Producción solo con REPO_ROOT, STATE_FILE y rama main (o un DEPLOY_REF) de producción.
    if [[ "$(canon "$REPO_ROOT")" != "$(canon "$PROD_REPO_ROOT")" || "$STATE_FILE" != "$PROD_STATE_FILE" \
          || ( -z "$DEPLOY_REF" && "$REPO_BRANCH" != "main" ) ]]; then
        if [[ "$ALLOW_CUSTOM" != "1" ]]; then
            die "SITE_ROOT es PRODUCCIÓN pero REPO_ROOT=$REPO_ROOT, STATE_FILE=$STATE_FILE o REPO_BRANCH=$REPO_BRANCH (DEPLOY_REF='${DEPLOY_REF}') no son los de producción (REPO_ROOT=$PROD_REPO_ROOT, STATE_FILE=$PROD_STATE_FILE, rama main o DEPLOY_REF). ¿Variables de staging en el entorno? Si es intencional: ALLOW_CUSTOM=1. Aborto sin tocar nada."
        fi
        warn "ALLOW_CUSTOM=1: configuración no estándar en PRODUCCIÓN."
    fi
fi

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
    --exclude='.deploy.env'
    --exclude='php-local.ini'
    --exclude='run-local.ps1'
    --exclude='storage/logs/'
    --exclude='storage/framework/sessions/'
    --exclude='storage/framework/cache/'
    --exclude='storage/framework/views/'
    --exclude='storage/backups/'
    --exclude='public/img/'
    --exclude='public/.htaccess'
    --exclude='public/storage'
)

# ---------- Helpers --------------------------------------------------------- #
IS_TTY=0
[[ -t 0 && -t 1 ]] && IS_TTY=1

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
    [[ $DRY_RUN -eq 1 ]] && return 0
    local ts; ts="$(date '+%Y-%m-%d %H:%M:%S')"
    mkdir -p "$(dirname "$LOG_FILE")"
    echo "[$ts] $*" >> "$LOG_FILE"
}

artisan() {
    run "$PHP_BIN $SITE_ROOT/artisan $*"
}

# Escritura atómica (tmp + mv) de un valor en un fichero de estado.
write_atomic() {
    local file="$1" value="$2"
    printf '%s\n' "$value" > "$file.tmp.$$"
    mv -f "$file.tmp.$$" "$file"
}

read_sha() {
    [[ -f "$1" ]] && tr -d '[:space:]' < "$1" || true
}

# Valor de una clave de $SITE_ROOT/.env sin comillas ni CR (vacío si no existe).
env_get() {
    awk -v k="$1" 'index($0, k "=") == 1 { sub(/^[^=]*=/, ""); print; exit }' "$SITE_ROOT/.env" 2>/dev/null | tr -d "\"'\r" || true
}

is_down() {
    [[ -f "$SITE_ROOT/storage/framework/down" ]]
}

TMP_PATHS=()
cleanup_tmp() {
    local p
    for p in ${TMP_PATHS[@]+"${TMP_PATHS[@]}"}; do
        [[ -n "$p" ]] && rm -rf "$p"
    done
    TMP_PATHS=()
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

# Ejecuta composer en un directorio con el MISMO php que $PHP_BIN (el shebang de composer
# podría resolver otro php): se antepone su directorio al PATH.
composer_in() {
    local dir="$1"; shift
    local phpdir; phpdir="$(dirname "$(command -v "$PHP_BIN" 2>/dev/null || echo "$PHP_BIN")")"
    if [[ $DRY_RUN -eq 1 ]]; then
        dry_say "(cd $dir && $COMPOSER_BIN $*)"
        return 0
    fi
    # shellcheck disable=SC2086
    ( cd "$dir" && PATH="$phpdir:$PATH" $COMPOSER_BIN "$@" )
}

# Detecta si estamos en stack moderno (PHP >= 8) para permitir composer/vendor.
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

# Versión de Laravel instalada en $1/vendor (def. SITE_ROOT).
live_laravel_version() {
    local root="${1:-$SITE_ROOT}"
    local f="$root/vendor/laravel/framework/src/Illuminate/Foundation/Application.php"
    if [[ ! -f "$f" ]]; then
        echo ""
        return
    fi
    sed -n -E "/const VERSION = '/{s/.*'([^']+)'.*/\1/p;q}" "$f" || echo ""
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
# $1 = composer.lock del destino. Compara el major instalado con el de ese lock.
detect_major_upgrade() {
    local new_lock="$1" live_major new_major
    live_major="$(major_of "$(live_laravel_version)")"
    new_major="$(lock_laravel_major "$new_lock")"
    if [[ -z "$live_major" || -z "$new_major" || "$live_major" != "$new_major" ]]; then
        return 0
    fi
    local lock_live="$SITE_ROOT/composer.lock"
    if [[ ! -f "$lock_live" || ! -f "$new_lock" ]]; then
        return 0
    fi
    if ! cmp -s "$lock_live" "$new_lock"; then
        return 0
    fi
    return 1
}

# El major de Laravel instalado en $1/vendor debe coincidir con el de $1/composer.lock.
# (def. SITE_ROOT). $2 = lock alternativo opcional.
assert_laravel_matches_lock() {
    local root="${1:-$SITE_ROOT}" lock="${2:-}" ver inst_major lock_major
    [[ -n "$lock" ]] || lock="$root/composer.lock"
    ver="$(live_laravel_version "$root")"
    inst_major="$(major_of "$ver")"
    lock_major="$(lock_laravel_major "$lock")"
    [[ -n "$lock_major" ]] || die "No se pudo leer laravel/framework de $lock. Aborto sin marcar SHA."
    [[ -n "$inst_major" ]] || die "No se detecta Laravel en $root/vendor (VERSION desconocida). Aborto sin marcar SHA."
    if [[ "$inst_major" != "$lock_major" ]]; then
        die "vendor/ tiene Laravel $ver pero composer.lock pide ${lock_major}.x. Aborto sin marcar SHA."
    fi
    ok "Laravel VERSION=$ver (major $inst_major coincide con composer.lock)"
}

# El php del composer.json de destino ($1) debe cumplirlo el CLI que ejecuta composer.
assert_php_requirement() {
    local cj="$1" msg
    msg="$("$PHP_BIN" -r '
        $j = json_decode((string) @file_get_contents($argv[1]), true);
        $c = $j["require"]["php"] ?? "";
        if (! preg_match("/(\d+\.\d+)/", $c, $m)) { exit(0); }
        if (version_compare(PHP_VERSION, $m[1], ">=")) { exit(0); }
        echo "composer.json exige PHP $c y el CLI ($argv[2]) es " . PHP_VERSION;
        exit(1);
    ' "$cj" "$PHP_BIN" 2>&1)" || die "$msg. Define PHP_BIN con PHP 8.3 (/opt/alt/php83/usr/bin/php)."
    ok "PHP $("$PHP_BIN" -r 'echo PHP_VERSION;') cumple el requisito de composer.json"
}

# Espacio libre en el filesystem de SITE_ROOT frente a 3x el tamaño de vendor/.
check_disk_space() {
    [[ -d "$SITE_ROOT/vendor" ]] || return 0
    local v_kb avail_kb
    v_kb="$(du -sk "$SITE_ROOT/vendor" | cut -f1)"
    avail_kb="$(df -Pk "$SITE_ROOT" | awk 'NR==2 {print $4}')"
    if [[ -n "$avail_kb" && "$avail_kb" -lt $((v_kb * 3)) ]]; then
        die "Poco espacio libre: ${avail_kb}K disponibles, se necesitan ~$((v_kb * 3))K (3x vendor/). No se ha tocado nada."
    fi
    ok "Espacio libre suficiente (${avail_kb}K; vendor/ ocupa ${v_kb}K)"
}

# El autoload optimizado de $1/vendor debe incluir clases App\
assert_autoload_has_app() {
    local root="${1:-$SITE_ROOT}"
    "$PHP_BIN" -r '
        $m = @include $argv[1] . "/vendor/composer/autoload_classmap.php";
        if (is_array($m)) { foreach ($m as $k => $_) { if (strpos($k, "App\\") === 0) { exit(0); } } }
        exit(1);
    ' "$root" || die "El autoload optimizado de $root/vendor no incluye clases App\\ (¿falta dump-autoload?)."
    ok "Autoload optimizado incluye App\\"
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
        if ! tar -tzf "$tmp" vendor/autoload.php >/dev/null 2>&1; then
            rm -f "$tmp"
            die "La instantánea creada no es válida (falta vendor/autoload.php o el tar está corrupto). No se ha tocado nada."
        fi
        mv -f "$tmp" "$snap"
        ok "Instantánea de vendor/ en $snap ($(du -h "$snap" | cut -f1))"
    fi
    while IFS= read -r old; do
        [[ -n "$old" ]] && rm -f "$old"
    done < <(ls -1t "$BACKUP_DIR"/vendor-*.tar.gz 2>/dev/null | tail -n +"$((VENDOR_SNAPSHOTS_KEEP + 1))")
    return 0
}

# Extrae una instantánea a $2 y la valida (autoload.php y major de Laravel == $3).
extract_and_validate_snapshot() {
    local snap="$1" dest="$2" expected_major="$3" ver
    rm -rf "$dest"
    mkdir -p "$dest"
    tar -xzf "$snap" -C "$dest" || die "No se pudo extraer $snap"
    [[ -f "$dest/vendor/autoload.php" ]] || die "La instantánea $snap no contiene vendor/autoload.php; no la aplico."
    ver="$(live_laravel_version "$dest")"
    if [[ -n "$expected_major" && "$(major_of "$ver")" != "$expected_major" ]]; then
        die "La instantánea $snap tiene Laravel '${ver:-desconocido}' y se esperaba ${expected_major}.x; no la aplico."
    fi
    ok "Instantánea válida (Laravel $ver)"
}

do_rsync() {
    local -a flags=(-av)
    [[ $DRY_RUN -eq 1 ]] && flags=(-avn --itemize-changes)
    rsync "${flags[@]}" "${EXCLUDES[@]}" "$@"
}

# Intercambia $SITE_ROOT/vendor por $1/vendor (con mv, en el mismo filesystem).
swap_vendor() {
    local stage="$1" old="$SITE_ROOT/vendor.rollback-old"
    rm -rf "$old"
    if [[ -d "$SITE_ROOT/vendor" ]]; then
        mv "$SITE_ROOT/vendor" "$old"
    fi
    if ! mv "$stage/vendor" "$SITE_ROOT/vendor"; then
        if [[ -d "$old" && ! -e "$SITE_ROOT/vendor" ]]; then mv "$old" "$SITE_ROOT/vendor" 2>/dev/null || true; fi
        die "Falló el segundo mv de vendor/. El vendor anterior está en $old (o se ha devuelto a vendor/); revisa a mano."
    fi
    rm -rf "$old"
    ok "vendor/ intercambiado"
}

# Smoke test HTTP contra APP_URL (.env del sitio). return 0 = todo 200 y sitio sin mantenimiento.
SMOKE_NOTE=""
smoke_test() {
    local app_url p url code fails=0
    local -a auth=()
    local -a paths=(/ /login /register)
    SMOKE_NOTE=""
    app_url="$(env_get APP_URL)"
    app_url="${app_url%/}"
    if [[ -z "$app_url" ]]; then
        warn "No se pudo leer APP_URL de $SITE_ROOT/.env; no se puede hacer el smoke test."
        return 1
    fi
    if ! command -v curl >/dev/null 2>&1; then
        warn "curl no está instalado; no se puede hacer el smoke test."
        return 1
    fi
    if is_down; then
        SMOKE_NOTE="EL SITIO SIGUE EN MANTENIMIENTO (existe storage/framework/down): NO está arriba."
        printf "${C_RED}✗${C_RESET} %s\n" "$SMOKE_NOTE" >&2
        fails=$((fails + 1))
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
            [[ "$code" = "503" ]] && SMOKE_NOTE="${SMOKE_NOTE:+$SMOKE_NOTE }Respuesta 503 en $p: el sitio está en mantenimiento o caído."
            printf "${C_RED}✗${C_RESET} %s  %s\n" "$code" "$url" >&2
            fails=$((fails + 1))
        fi
    done
    [[ $fails -eq 0 ]]
}

smoke_failure_banner() {
    {
        printf "\n${C_RED}════════════════════════════════════════════════════════════${C_RESET}\n"
        printf "${C_RED}✗ SMOKE TEST FALLIDO: alguna URL no responde 200.${C_RESET}\n"
        if is_down; then
            echo "  OJO: el sitio está EN MANTENIMIENTO (no está arriba). Reábrelo: $PHP_BIN $SITE_ROOT/artisan up"
        else
            echo "  El sitio está arriba con el código nuevo. NO se ha hecho rollback automático."
        fi
        [[ -n "$SMOKE_NOTE" ]] && echo "  $SMOKE_NOTE"
        echo "  Revisa: tail -n 80 $SITE_ROOT/storage/logs/laravel.log"
        echo "  Si es grave: ./scripts/deploy.sh --rollback   (no restaura la BD)"
        printf "${C_RED}════════════════════════════════════════════════════════════${C_RESET}\n"
    } >&2
}

# Muestra el objetivo y pide confirmación (host de APP_URL) si hay TTY y no se pasó --yes.
confirm_target() {
    local live="$1" target="$2" label="$3" app_url host typed
    app_url="$(env_get APP_URL)"
    say "SITE_ROOT : $SITE_ROOT"
    say "APP_URL   : ${app_url:-<no definida>}"
    say "REPO_ROOT : $REPO_ROOT   (${DEPLOY_REF:+ref $DEPLOY_REF}${DEPLOY_REF:-rama $REPO_REMOTE/$REPO_BRANCH})"
    say "STATE_FILE: $STATE_FILE"
    say "$label: en vivo ${live:0:7} → destino ${target:0:7}"
    [[ $DRY_RUN -eq 1 || $ASSUME_YES -eq 1 ]] && return 0
    if [[ $IS_TTY -ne 1 ]]; then
        warn "Sin TTY: no se pide confirmación."
        return 0
    fi
    host="${app_url#*://}"; host="${host%%/*}"
    [[ -n "$host" ]] || die "APP_URL sin host en $SITE_ROOT/.env; usa --yes si es intencional."
    read -r -p "Escribe el host de APP_URL ($host) para continuar: " typed
    [[ "$typed" == "$host" ]] || die "Confirmación incorrecta. Aborto sin tocar nada."
}

# Entra en mantenimiento. $1=1 estricto (deploy: si falla y no queda fichero down, aborta).
WAS_DOWN=0
MAINTENANCE=0
enter_maintenance() {
    local strict="$1" status
    [[ $NO_DOWN -eq 1 ]] && return 0
    if [[ $DRY_RUN -eq 1 ]]; then
        dry_say "$PHP_BIN $SITE_ROOT/artisan down --render=\"errors::503\" --retry=60"
        MAINTENANCE=1
        return 0
    fi
    if [[ ! -f "$SITE_ROOT/vendor/autoload.php" ]]; then
        warn "Sin vendor/ en el sitio (primer despliegue): no hay nada que poner en mantenimiento."
        return 0
    fi
    is_down && WAS_DOWN=1
    say "Entrando en modo mantenimiento"
    set +e
    "$PHP_BIN" "$SITE_ROOT/artisan" down --render="errors::503" --retry=60
    status=$?
    set -e
    if [[ $status -eq 0 ]]; then
        MAINTENANCE=1
        ok "Sitio en mantenimiento"
    elif is_down; then
        MAINTENANCE=1
        warn "artisan down falló (exit $status) pero existe storage/framework/down: el sitio ya está en mantenimiento."
    elif [[ "$strict" = "1" ]]; then
        die "artisan down falló (exit $status) y el sitio NO está en mantenimiento. Aborto sin tocar nada (usa --no-down bajo tu responsabilidad)."
    else
        warn "artisan down falló (exit $status). Continuando (rollback de emergencia)."
    fi
}

# ---------- Estado y trap de salida ---------------------------------------- #
EXIT_MODE="deploy"      # deploy | rollback
DEPLOY_TOUCHED=0        # 1 = ya se empezó a modificar el sitio (rsync/composer)
DEPLOY_DONE=0           # 1 = deploy terminado y SHA marcado (solo falta `up`)
BACKUP_FILE=""

on_exit() {
    local rc=$?
    trap - EXIT
    cleanup_tmp
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
        if [[ $MAINTENANCE -eq 1 && $WAS_DOWN -eq 0 ]]; then
            "$PHP_BIN" "$SITE_ROOT/artisan" up >/dev/null 2>&1 || true
            warn "Fallo antes de modificar el sitio; se ha reabierto (artisan up)."
        fi
        warn "No se ha modificado el sitio (exit $rc)."
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
        if is_down; then
            echo "  El sitio SE QUEDA EN MANTENIMIENTO (no se reabre a medias)."
        else
            echo "  OJO: el sitio NO está en mantenimiento (--no-down o 'down' falló) y puede estar en un estado inconsistente."
        fi
        echo "  Qué hacer:"
        if [[ "$EXIT_MODE" = "rollback" ]]; then
            echo "   1) Revisa el error de arriba y relanza: ./scripts/deploy.sh --rollback   (reutiliza el destino guardado en $STATE_FILE.rollback-target)"
        else
            echo "   1) Vuelve atrás: ./scripts/deploy.sh --rollback   (código + vendor/ del último SHA bueno: $(read_sha "$STATE_FILE" | cut -c1-7))"
            echo "   2) Reabre solo si lo has revisado: $PHP_BIN $SITE_ROOT/artisan up"
        fi
        if [[ -n "$BACKUP_FILE" && -f "$BACKUP_FILE" ]]; then
            echo "   BD: backup de este deploy en $BACKUP_FILE"
        else
            echo "   BD: sin backup de este deploy; últimos dumps en $BACKUP_DIR/db-*.sql.gz"
        fi
        echo "   El rollback NO restaura la BD. Si se aplicaron migraciones, restaura el dump a mano:"
        echo "     gunzip -c <dump>.sql.gz | mysql -u USER -p DB_NAME"
        [[ -n "${OUT_FILE:-}" ]] && echo "   Log completo: $OUT_FILE"
        printf "${C_RED}════════════════════════════════════════════════════════════${C_RESET}\n"
    } >&2
    exit "$rc"
}
trap on_exit EXIT
trap 'exit 130' INT
trap 'exit 143' TERM

# Reutilizable por tests: si se hace `source` con DEPLOY_SOURCE_ONLY=1 no se ejecuta nada más.
if [[ "${DEPLOY_SOURCE_ONLY:-0}" = "1" ]]; then
    return 0 2>/dev/null || exit 0
fi

# ---------- Comprobaciones previas ----------------------------------------- #
[[ -d "$SITE_ROOT" ]]  || die "No existe SITE_ROOT: $SITE_ROOT"
[[ -d "$REPO_ROOT" ]]  || die "No existe REPO_ROOT: $REPO_ROOT (clona el repo allí)"
[[ -d "$REPO_ROOT/.git" ]] || die "$REPO_ROOT no es un repositorio git"
command -v rsync >/dev/null      || die "rsync no está instalado"
command -v git >/dev/null        || die "git no está instalado"

resolve_php_bin
command -v "$PHP_BIN" >/dev/null 2>&1 || [[ -x "$PHP_BIN" ]] || die "$PHP_BIN no está disponible"
say "PHP: $PHP_BIN ($("$PHP_BIN" -r 'echo PHP_VERSION;' 2>/dev/null || echo desconocido))"
resolve_composer_invocation
say "Composer: $COMPOSER_BIN"
[[ $DEPLOY_ENV_LOADED -eq 1 ]] && say "Configuración: $SITE_ROOT/.deploy.env"

# Un solo deploy a la vez y salida duplicada a un fichero (por si se corta el SSH).
STAMP="$(date +%Y%m%d-%H%M%S)"
if [[ $DRY_RUN -eq 0 ]]; then
    mkdir -p "$BACKUP_DIR"
    OUT_FILE="$BACKUP_DIR/deploy-$STAMP.out"
    if command -v flock >/dev/null 2>&1; then
        exec 9>"$STATE_FILE.lock"
        flock -n 9 || die "Hay otro deploy/rollback en curso ($STATE_FILE.lock)."
    else
        warn "flock no disponible: no se puede impedir deploys concurrentes."
    fi
else
    OUT_FILE="/tmp/deploy-dry-$STAMP.out"
fi
exec > >(tee -ia "$OUT_FILE") 2>&1
say "Salida guardada en $OUT_FILE"

# Limpia directorios temporales huérfanos de ejecuciones anteriores (PID ya inexistente).
for _d in "$SITE_ROOT"/.vendor-new-* "$SITE_ROOT"/.vendor-restore-*; do
    [[ -d "$_d" ]] || continue
    _pid="${_d##*-}"
    if [[ "$_pid" =~ ^[0-9]+$ ]] && ! kill -0 "$_pid" 2>/dev/null; then
        if [[ $DRY_RUN -eq 1 ]]; then
            dry_say "rm -rf $_d (huérfano)"
        else
            warn "Eliminando temporal huérfano $_d"
            rm -rf "$_d"
        fi
    fi
done

# ---------- ROLLBACK ------------------------------------------------------- #
if [[ $ROLLBACK -eq 1 ]]; then
    EXIT_MODE="rollback"
    cd "$REPO_ROOT"
    git fetch --quiet "$REPO_REMOTE" || warn "git fetch falló; se usa el repo local"

    STATE_SHA="$(read_sha "$STATE_FILE")"
    PENDING_SHA="$(read_sha "$STATE_FILE.pending")"
    PREV=""
    PREV_SOURCE=""
    if [[ -n "$ROLLBACK_TO" ]]; then
        PREV="$ROLLBACK_TO"; PREV_SOURCE="ROLLBACK_TO"
    elif [[ -f "$STATE_FILE.rollback-target" ]]; then
        PREV="$(read_sha "$STATE_FILE.rollback-target")"; PREV_SOURCE="rollback anterior interrumpido (.rollback-target)"
    elif [[ -n "$PENDING_SHA" && "$PENDING_SHA" != "$STATE_SHA" ]]; then
        PREV="$STATE_SHA"; PREV_SOURCE="deploy fallido: último SHA bueno (STATE_FILE)"
    elif [[ -f "$STATE_FILE.prev" ]]; then
        PREV="$(read_sha "$STATE_FILE.prev")"; PREV_SOURCE="SHA anterior (.prev)"
    fi
    [[ -n "$PREV" ]] || die "No hay SHA destino registrado ($STATE_FILE.prev). Indica uno con: ROLLBACK_TO=<sha> ./scripts/deploy.sh --rollback"
    PREV="$(git rev-parse --verify "${PREV}^{commit}" 2>/dev/null)" || die "El SHA destino no existe en $REPO_ROOT"
    LIVE_SHA="${PENDING_SHA:-$STATE_SHA}"
    say "Destino del rollback: ${PREV:0:7} [$PREV_SOURCE]"
    if [[ -z "$PENDING_SHA" && "$STATE_SHA" = "$PREV" && ! -f "$STATE_FILE.rollback-target" ]]; then
        warn "El sitio ya está en $PREV; ¿buscas un rollback más antiguo? Usa ROLLBACK_TO=<sha>."
        exit 0
    fi
    confirm_target "$LIVE_SHA" "$PREV" "ROLLBACK"

    SNAP="$BACKUP_DIR/vendor-$PREV.tar.gz"
    PREV_LOCK="$(mktemp)"; TMP_PATHS+=("$PREV_LOCK")
    git show "$PREV:composer.lock" > "$PREV_LOCK" 2>/dev/null || : > "$PREV_LOCK"
    EXPECTED_MAJOR="$(lock_laravel_major "$PREV_LOCK")"
    printf "\n${C_YELLOW}!! ATENCIÓN: el rollback NO restaura la base de datos.${C_RESET}\n"
    echo "   Las migraciones nuevas NO se revierten solas: el código antiguo correrá contra el esquema nuevo."
    LAST_DUMP="$(ls -1t "$BACKUP_DIR"/db-*.sql.gz 2>/dev/null | sed -n 1p || true)"
    echo "   Último dump de BD: ${LAST_DUMP:-ninguno en $BACKUP_DIR}"
    echo

    # Se prepara el vendor destino FUERA del sitio y se valida antes de tocar nada.
    STAGE="$SITE_ROOT/.vendor-restore-$$"
    VENDOR_FROM_SNAPSHOT=0
    if [[ $DRY_RUN -eq 1 ]]; then
        if [[ -f "$SNAP" ]]; then
            dry_say "extraer y validar $SNAP (autoload.php y Laravel ${EXPECTED_MAJOR:-?}.x)"
        else
            dry_say "no hay $SNAP: composer install con el lock de ${PREV:0:7} en un directorio temporal"
        fi
    else
        TMP_PATHS+=("$STAGE")
        TMPJ="$(mktemp)"; TMP_PATHS+=("$TMPJ")
        git show "$PREV:composer.json" > "$TMPJ"
        assert_php_requirement "$TMPJ"
        if [[ -f "$SNAP" ]]; then
            say "Validando la instantánea $SNAP"
            extract_and_validate_snapshot "$SNAP" "$STAGE" "$EXPECTED_MAJOR"
            VENDOR_FROM_SNAPSHOT=1
        else
            warn "No existe $SNAP; se reinstala vendor/ con composer install usando el composer.lock de ${PREV:0:7} (necesita red)."
            mkdir -p "$STAGE"
            git show "$PREV:composer.json" > "$STAGE/composer.json"
            git show "$PREV:composer.lock" > "$STAGE/composer.lock"
            composer_in "$STAGE" install --no-dev --no-scripts --prefer-dist --optimize-autoloader --no-interaction
            assert_laravel_matches_lock "$STAGE"
        fi
    fi

    enter_maintenance 0

    # Desde aquí se modifica el sitio: un fallo lo deja en mantenimiento.
    if [[ $DRY_RUN -eq 0 ]]; then
        DEPLOY_TOUCHED=1
        write_atomic "$STATE_FILE.rollback-target" "$PREV"
    fi
    run "cd $REPO_ROOT && git reset --hard $PREV"
    if [[ $DRY_RUN -eq 1 ]]; then
        RB_SRC="$(mktemp -d)"; TMP_PATHS+=("$RB_SRC")
        git archive "$PREV" | tar -x -C "$RB_SRC"
    else
        RB_SRC="$REPO_ROOT"
    fi
    do_rsync "$RB_SRC/" "$SITE_ROOT/"
    run "rm -f $SITE_ROOT/bootstrap/cache/services.php $SITE_ROOT/bootstrap/cache/packages.php $SITE_ROOT/bootstrap/cache/config.php"

    if [[ $DRY_RUN -eq 0 ]]; then
        swap_vendor "$STAGE"
        cp -f "$STAGE/composer.json" "$SITE_ROOT/composer.json" 2>/dev/null || true
        cp -f "$STAGE/composer.lock" "$SITE_ROOT/composer.lock" 2>/dev/null || true
        if [[ $VENDOR_FROM_SNAPSHOT -eq 0 ]]; then
            composer_in "$SITE_ROOT" dump-autoload --optimize --no-dev --no-scripts --no-interaction
        fi
        assert_laravel_matches_lock
    else
        dry_say "intercambiar vendor/ (mv), dump-autoload si se reinstaló, comprobar versión"
    fi

    artisan "package:discover"
    artisan "view:clear"
    artisan "cache:clear"
    artisan "config:cache"
    say "Comprobando salud tras el rollback"
    artisan "webnu:deploy-check"

    if [[ $DRY_RUN -eq 0 ]]; then
        rm -f "$STATE_FILE.prev" "$STATE_FILE.pending"
        write_atomic "$STATE_FILE" "$PREV"
        rm -f "$STATE_FILE.rollback-target"
        log_line "ROLLBACK a $PREV (desde ${LIVE_SHA:-?})"
    fi
    DEPLOY_DONE=1
    if [[ $DRY_RUN -eq 1 ]]; then
        dry_say "$PHP_BIN $SITE_ROOT/artisan up (si existe storage/framework/down)"
    elif is_down || [[ $MAINTENANCE -eq 1 ]]; then
        "$PHP_BIN" "$SITE_ROOT/artisan" up
        is_down && die "artisan up terminó pero sigue existiendo storage/framework/down."
    fi
    cleanup_tmp
    trap - EXIT
    ok "Rollback completo a ${PREV:0:7} (código + vendor/)"
    warn "La BD NO se ha tocado: si el deploy fallido aplicó migraciones, restaura el dump (ver docs/deploy.md)."
    warn "El repo $REPO_ROOT queda en ${PREV:0:7}; el próximo deploy volverá a $REPO_REMOTE/$REPO_BRANCH."
    if [[ $DRY_RUN -eq 0 ]] && ! smoke_test; then
        smoke_failure_banner
        exit 3
    fi
    exit 0
fi

# ---------- Destino del deploy --------------------------------------------- #
say "Sincronizando $REPO_ROOT con ${DEPLOY_REF:-$REPO_REMOTE/$REPO_BRANCH}"
cd "$REPO_ROOT"
git fetch --quiet --tags "$REPO_REMOTE" || die "git fetch falló en $REPO_ROOT"
if [[ -n "$DEPLOY_REF" ]]; then
    NEW_SHA="$(git rev-parse --verify "${DEPLOY_REF}^{commit}" 2>/dev/null)" || die "DEPLOY_REF no existe en $REPO_ROOT: $DEPLOY_REF"
else
    NEW_SHA="$(git rev-parse --verify "$REPO_REMOTE/$REPO_BRANCH^{commit}" 2>/dev/null)" || die "No existe $REPO_REMOTE/$REPO_BRANCH en $REPO_ROOT"
fi

# El deploy.sh del servidor debe ser el del destino (rsync excluye scripts/).
if git cat-file -e "$NEW_SHA:scripts/deploy.sh" 2>/dev/null; then
    if ! git show "$NEW_SHA:scripts/deploy.sh" | cmp -s - "$SCRIPT_PATH"; then
        if [[ "${SKIP_SCRIPT_CHECK:-0}" = "1" ]]; then
            if [[ $IS_PROD_SITE -eq 1 ]]; then
                [[ $IS_TTY -eq 1 && $DRY_RUN -eq 0 ]] || die "SKIP_SCRIPT_CHECK en PRODUCCIÓN solo se admite con confirmación por TTY."
                read -r -p "SKIP_SCRIPT_CHECK en PRODUCCIÓN: escribe SALTAR para continuar: " _ans
                [[ "$_ans" == "SALTAR" ]] || die "Confirmación incorrecta. Aborto sin tocar nada."
            fi
            warn "SKIP_SCRIPT_CHECK: se despliega con un deploy.sh distinto al de ${NEW_SHA:0:7} (si el destino es anterior, podrías sobrescribir public/index.php con una versión vieja)."
        else
            die "Este deploy.sh difiere del de ${NEW_SHA:0:7}. Actualízalo desde el commit de destino (el clon aún no está en ese commit): git -C \"$REPO_ROOT\" show \"$NEW_SHA:scripts/deploy.sh\" > \"$SCRIPT_PATH\" && chmod +x \"$SCRIPT_PATH\" . Si el destino es anterior al deploy.sh nuevo NO uses SKIP_SCRIPT_CHECK: despliega un ref que ya lo contenga (ver docs/STAGING.md §8)."
        fi
    fi
else
    warn "El destino no contiene scripts/deploy.sh; no se puede comparar."
fi

PREV_SHA_RAW="$(read_sha "$STATE_FILE")"
if [[ -z "$PREV_SHA_RAW" ]]; then
    PREV_SHA_RAW="$(git rev-parse HEAD 2>/dev/null || echo "")"
fi
PREV_SHA=""
if [[ -n "$PREV_SHA_RAW" ]]; then
    PREV_SHA="$(git rev-parse --verify "${PREV_SHA_RAW}^{commit}" 2>/dev/null || echo "")"
fi

if [[ -f "$STATE_FILE.pending" ]]; then
    warn "Existe $STATE_FILE.pending: un deploy anterior no terminó bien. Considera --rollback antes de reintentar."
fi

if [[ "$PREV_SHA" = "$NEW_SHA" ]]; then
    warn "El SHA en vivo ($PREV_SHA) ya coincide con el destino. Nada que desplegar."
    if [[ $FULL_RSYNC -eq 0 ]]; then
        exit 0
    fi
    warn "Continuando porque --full-rsync fue solicitado."
fi

# Ficheros de destino leídos del commit (sin tocar el working tree en dry-run).
NEW_LOCK="$(mktemp)"; NEW_JSON="$(mktemp)"; TMP_PATHS+=("$NEW_LOCK" "$NEW_JSON")
git show "$NEW_SHA:composer.lock" > "$NEW_LOCK" 2>/dev/null || : > "$NEW_LOCK"
git show "$NEW_SHA:composer.json" > "$NEW_JSON" 2>/dev/null || : > "$NEW_JSON"

if detect_major_upgrade "$NEW_LOCK"; then
    warn "Upgrade mayor detectado (major de Laravel live ≠ el del destino y/o composer.lock distinto). Forzando --full-rsync --with-vendor."
    FULL_RSYNC=1
    WITH_VENDOR=1
    [[ -n "$PREV_SHA" ]] || die "Upgrade mayor sin SHA previo válido (no hay STATE_FILE ni HEAD usable): no habría a dónde hacer rollback. Aborto sin tocar nada."
fi

confirm_target "${PREV_SHA:-ninguno}" "$NEW_SHA" "DEPLOY"

if should_sync_vendor; then
    say "Comprobaciones de composer"
    assert_php_requirement "$NEW_JSON"
    check_disk_space
fi

# vendor/ nuevo, construido FUERA del sitio en vivo (mismo filesystem, para mover con mv).
STAGE="$SITE_ROOT/.vendor-new-$$"
if should_sync_vendor; then
    if [[ $DRY_RUN -eq 1 ]]; then
        dry_say "composer install --no-dev --no-scripts --prefer-dist --optimize-autoloader en $STAGE (con composer.json/lock de ${NEW_SHA:0:7})"
    else
        say "Preparando vendor/ nuevo en $STAGE (el sitio sigue sirviendo el vendor actual)"
        TMP_PATHS+=("$STAGE")
        mkdir -p "$STAGE"
        cp "$NEW_JSON" "$STAGE/composer.json"
        cp "$NEW_LOCK" "$STAGE/composer.lock"
        composer_in "$STAGE" install --no-dev --no-scripts --prefer-dist --optimize-autoloader --no-interaction
        assert_laravel_matches_lock "$STAGE"
    fi
fi

# Working tree del repo = destino (en dry-run solo se exporta a /tmp).
if [[ $DRY_RUN -eq 1 ]]; then
    SRC="$(mktemp -d)"; TMP_PATHS+=("$SRC")
    git archive "$NEW_SHA" | tar -x -C "$SRC"
else
    git reset --hard --quiet "$NEW_SHA"
    SRC="$REPO_ROOT"
fi
say "PREV=$PREV_SHA  NEW=$NEW_SHA"

# ---------- Mantenimiento --------------------------------------------------- #
enter_maintenance 1

# El trap on_exit decide al fallar: antes del rsync reabre el sitio (no se ha
# tocado nada); después lo deja en mantenimiento e imprime cómo recuperarlo.

# ---------- Backup BD ------------------------------------------------------- #
if [[ $SKIP_BACKUP -eq 0 ]]; then
    say "Backup MySQL"
    BACKUP_FILE="$BACKUP_DIR/db-$STAMP.sql.gz"

    DB_NAME="$(env_get DB_DATABASE)"
    DB_USER="$(env_get DB_USERNAME)"
    DB_PASS="$(env_get DB_PASSWORD)"
    DB_HOST="$(env_get DB_HOST)"

    [[ -n "$DB_NAME" ]] || die "No se pudo leer DB_DATABASE de .env; no despliego sin backup. Usa --skip-backup si es intencional."
    [[ -n "$DB_HOST" ]] || die "Falta DB_HOST en $SITE_ROOT/.env (p. ej. DB_HOST=127.0.0.1). No despliego sin backup."

    if [[ $DRY_RUN -eq 1 ]]; then
        dry_say "mysqldump $DB_NAME → $BACKUP_FILE"
    else
        mkdir -p "$BACKUP_DIR"
        DUMP_ERR="$(mktemp)"; TMP_PATHS+=("$DUMP_ERR")
        set +e
        MYSQL_PWD="$DB_PASS" mysqldump --single-transaction --quick \
            -h "$DB_HOST" -u "$DB_USER" "$DB_NAME" 2>"$DUMP_ERR" \
            | gzip > "$BACKUP_FILE"
        DUMP_STATUS=${PIPESTATUS[0]}
        set -e
        if [[ $DUMP_STATUS -ne 0 ]]; then
            warn "mysqldump falló (exit $DUMP_STATUS):"
            cat "$DUMP_ERR" >&2 || true
            rm -f "$BACKUP_FILE"
            die "Backup MySQL falló. Usa --skip-backup solo si ya tienes un dump válido, o corrige DB_* en .env"
        fi
        BACKUP_BYTES="$(wc -c < "$BACKUP_FILE" | tr -d ' ')"
        if [[ "${BACKUP_BYTES:-0}" -lt 1024 ]]; then
            rm -f "$BACKUP_FILE"
            die "Backup MySQL demasiado pequeño (${BACKUP_BYTES} bytes). Aborto."
        fi
        ok "Backup en $BACKUP_FILE ($(du -h "$BACKUP_FILE" | cut -f1))"
    fi
else
    warn "Backup BD omitido (--skip-backup)"
fi

# ---------- Instantánea de vendor/ (para --rollback) ----------------------- #
say "Instantánea de vendor/ y composer.* (SHA previo ${PREV_SHA:0:7})"
snapshot_vendor "$PREV_SHA"

# A partir de aquí se modifica el sitio: un fallo ya no reabre el sitio.
if [[ $DRY_RUN -eq 0 ]]; then
    write_atomic "$STATE_FILE.pending" "$NEW_SHA"
    DEPLOY_TOUCHED=1
fi

# ---------- Sincronización de archivos ------------------------------------- #
NEW_FILES_LIST=""
DELETED_FILES_LIST=""

if [[ $FULL_RSYNC -eq 0 && -n "$PREV_SHA" && "$PREV_SHA" != "$NEW_SHA" ]]; then
    say "Calculando archivos cambiados entre $PREV_SHA y $NEW_SHA"
    CHANGED_TMP="$(mktemp)"; DELETED_TMP="$(mktemp)"; TMP_PATHS+=("$CHANGED_TMP" "$DELETED_TMP")
    git diff --name-only --diff-filter=AM "$PREV_SHA" "$NEW_SHA" > "$CHANGED_TMP"
    git diff --name-only --diff-filter=D  "$PREV_SHA" "$NEW_SHA" > "$DELETED_TMP"

    # Quitar ficheros vetados (.env, vendor, etc.). En stack moderno sí sincronizamos composer.json/lock.
    sed -i -E \
        -e '/^\.env$/d' -e '/^\.env\.example$/d' -e '/^\.deploy\.env$/d' \
        -e '/^php-local\.ini$/d' -e '/^run-local\.ps1$/d' \
        -e '/^public\/img\//d' -e '/^public\/\.htaccess$/d' \
        -e '/^vendor\//d' \
        -e '/^storage\/(logs|framework|backups)\//d' \
        -e '/^(tests|docs|scripts)\//d' \
        -e '/^\.github\//d' \
        -e '/^node_modules\//d' \
        "$CHANGED_TMP" "$DELETED_TMP"
    if ! should_sync_vendor; then
        sed -i -E -e '/^composer\.(json|lock)$/d' "$CHANGED_TMP" "$DELETED_TMP"
    fi

    CHANGED_COUNT="$(wc -l < "$CHANGED_TMP" | tr -d ' ')"
    DELETED_COUNT="$(wc -l < "$DELETED_TMP" | tr -d ' ')"
    say "Archivos a copiar: $CHANGED_COUNT  /  a borrar: $DELETED_COUNT"

    NEW_FILES_LIST="$CHANGED_TMP"
    DELETED_FILES_LIST="$DELETED_TMP"

    if [[ $CHANGED_COUNT -gt 0 ]]; then
        do_rsync --files-from="$CHANGED_TMP" "$SRC/" "$SITE_ROOT/"
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
    do_rsync "$SRC/" "$SITE_ROOT/"
fi

# Para los seeders que tengan que correr y para `[skip ci]`-style commit msgs.
COMMIT_MSG="$(git log -1 --pretty=%B "$NEW_SHA" 2>/dev/null || echo "")"

# ---------- vendor, autoload y caches -------------------------------------- #
say "Limpiando bootstrap cache"
run "rm -f $SITE_ROOT/bootstrap/cache/services.php $SITE_ROOT/bootstrap/cache/packages.php $SITE_ROOT/bootstrap/cache/config.php"

if should_sync_vendor; then
    if [[ $DRY_RUN -eq 1 ]]; then
        dry_say "intercambiar vendor/ (mv), composer dump-autoload --optimize, comprobar App\\ y versión de Laravel"
    else
        say "Instalando el vendor/ preparado"
        cmp -s "$STAGE/composer.lock" "$SITE_ROOT/composer.lock" || die "composer.lock del sitio no coincide con el de ${NEW_SHA:0:7} tras el rsync."
        swap_vendor "$STAGE"
        # El classmap optimizado se generó fuera del sitio (sin app/): se regenera aquí.
        composer_in "$SITE_ROOT" dump-autoload --optimize --no-dev --no-scripts --no-interaction
        assert_autoload_has_app
        assert_laravel_matches_lock
    fi
else
    say "Regenerando autoload (composer dump-autoload)"
    composer_in "$SITE_ROOT" dump-autoload --no-scripts --optimize --no-dev --no-interaction --ignore-platform-reqs
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

say "Comprobando salud del despliegue"
artisan "webnu:deploy-check"

# ---------- Cierre --------------------------------------------------------- #
if [[ $DRY_RUN -eq 0 ]]; then
    # .prev primero y luego STATE, ambos atómicos; .pending se borra al final.
    if [[ -n "$PREV_SHA" && "$PREV_SHA" != "$NEW_SHA" ]]; then
        write_atomic "$STATE_FILE.prev" "$PREV_SHA"
    fi
    write_atomic "$STATE_FILE" "$NEW_SHA"
    rm -f "$STATE_FILE.pending" "$STATE_FILE.rollback-target"
    log_line "DEPLOY $PREV_SHA → $NEW_SHA  msg=\"${COMMIT_MSG%%$'\n'*}\""
fi

DEPLOY_DONE=1
if [[ $DRY_RUN -eq 1 ]]; then
    dry_say "$PHP_BIN $SITE_ROOT/artisan up (si existe storage/framework/down)"
elif is_down || [[ $MAINTENANCE -eq 1 ]]; then
    "$PHP_BIN" "$SITE_ROOT/artisan" up
    is_down && die "artisan up terminó pero sigue existiendo storage/framework/down."
fi
cleanup_tmp
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
    smoke_failure_banner
    exit 3
fi
