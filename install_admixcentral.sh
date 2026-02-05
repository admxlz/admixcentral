#!/usr/bin/env bash
set -Eeuo pipefail

LOG_FILE="/root/admixcentral_install.log"
exec > >(tee -a "$LOG_FILE") 2>&1
trap 'echo; echo "[x] FAILED at line $LINENO. See $LOG_FILE"; exit 1' ERR

log(){ echo -e "\n[+] $*\n"; }
die(){ echo -e "\n[x] $*\n"; exit 1; }

[[ $EUID -eq 0 ]] || die "Run as root"

REPO_URL="https://github.com/admxlz/admixcentral.git"
INSTALL_DIR="/var/www/admixcentral"
PHP_VER="8.3"

DB_NAME="${DB_NAME:-admixcentral}"
DB_USER="${DB_USER:-admixcentral}"
DB_PASS="${DB_PASS:-}"
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"

SUPERVISOR_WORKERS="${SUPERVISOR_WORKERS:-10}"

wait_for_apt() {
  while fuser /var/lib/dpkg/lock-frontend >/dev/null 2>&1 \
     || fuser /var/lib/dpkg/lock >/dev/null 2>&1 \
     || fuser /var/lib/apt/lists/lock >/dev/null 2>&1; do
    sleep 2
  done
}
apt_safe_install() { wait_for_apt; DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends "$@"; }

gen_pw() {
  if command -v openssl >/dev/null 2>&1; then
    openssl rand -base64 24 | tr -d '\n' | tr '+/' '_-' | cut -c1-32
  else
    python3 - <<'PY'
import secrets, string
alphabet = string.ascii_letters + string.digits + "_-"
print("".join(secrets.choice(alphabet) for _ in range(32)))
PY
  fi
}

# Write or replace KEY=VALUE in .env
set_env_kv() {
  local file="$1" key="$2" val="$3"
  if grep -qE "^${key}=" "$file"; then
    sed -i "s|^${key}=.*|${key}=${val}|g" "$file"
  else
    echo "${key}=${val}" >> "$file"
  fi
}

log "Updating system"
wait_for_apt
apt-get update -y

log "Installing base packages"
apt_safe_install ca-certificates curl gnupg unzip git openssl python3

log "Installing services"
apt_safe_install nginx mysql-server supervisor redis-server

log "Installing PHP ${PHP_VER}"
apt_safe_install \
  php${PHP_VER}-fpm php${PHP_VER}-cli php${PHP_VER}-common \
  php${PHP_VER}-curl php${PHP_VER}-mbstring php${PHP_VER}-xml php${PHP_VER}-zip \
  php${PHP_VER}-mysql php${PHP_VER}-sqlite3 php${PHP_VER}-bcmath php${PHP_VER}-intl php${PHP_VER}-gd

systemctl enable --now nginx mysql php${PHP_VER}-fpm supervisor redis-server

log "Installing Composer"
if ! command -v composer >/dev/null 2>&1; then
  curl -fsSL https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

log "Installing Node.js 20"
if ! command -v node >/dev/null 2>&1; then
  curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
  apt_safe_install nodejs
fi

log "Waiting for MySQL socket"
for _ in {1..60}; do
  [[ -S /var/run/mysqld/mysqld.sock ]] && break
  sleep 1
done
[[ -S /var/run/mysqld/mysqld.sock ]] || die "MySQL socket not ready"

log "Provisioning MySQL"
sudo mysql --protocol=socket -e \
  "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

if [[ -z "$DB_PASS" ]]; then DB_PASS="$(gen_pw)"; fi

sudo mysql --protocol=socket -e \
  "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"

sudo mysql --protocol=socket -e \
  "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost'; FLUSH PRIVILEGES;"

log "Deploying application"
rm -rf "$INSTALL_DIR"
git clone "$REPO_URL" "$INSTALL_DIR"

cd "$INSTALL_DIR"
[[ -f artisan && -d public ]] || die "Invalid repo layout (expected artisan + public/)"

# .env
cp .env.example .env

# FORCE mysql settings regardless of what .env.example contains
set_env_kv .env DB_CONNECTION "mysql"
set_env_kv .env DB_HOST "$DB_HOST"
set_env_kv .env DB_PORT "$DB_PORT"
set_env_kv .env DB_DATABASE "$DB_NAME"
set_env_kv .env DB_USERNAME "$DB_USER"
set_env_kv .env DB_PASSWORD "$DB_PASS"

# Safety: some packages may touch sqlite during discovery
mkdir -p "${INSTALL_DIR}/database"
touch "${INSTALL_DIR}/database/database.sqlite"

# CRITICAL: remove cached config BEFORE composer runs artisan scripts
rm -f bootstrap/cache/config.php bootstrap/cache/services.php bootstrap/cache/packages.php bootstrap/cache/events.php || true

# Also clear caches (won't hurt if app isn't ready yet)
php artisan config:clear || true
php artisan cache:clear || true

log "Installing PHP dependencies"
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader --no-interaction

log "Generating APP_KEY"
php artisan key:generate --force

log "Building frontend"
npm install
npm run build

log "Running migrations"
php artisan migrate --force
php artisan storage:link || true

chown -R www-data:www-data "$INSTALL_DIR"
chmod -R 775 storage bootstrap/cache

log "Configuring Nginx"
cat >/etc/nginx/sites-available/admixcentral <<EOF
server {
    listen 80 default_server;
    server_name _;
    root ${INSTALL_DIR}/public;
    index index.php;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php${PHP_VER}-fpm.sock;
    }
}
EOF

ln -sf /etc/nginx/sites-available/admixcentral /etc/nginx/sites-enabled/admixcentral
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx

log "Configuring Supervisor workers"
cat >/etc/supervisor/conf.d/admix-worker.conf <<EOF
[program:admix-worker]
command=/usr/bin/php ${INSTALL_DIR}/artisan queue:work --sleep=3 --tries=3
user=www-data
numprocs=${SUPERVISOR_WORKERS}
autostart=true
autorestart=true
stdout_logfile=${INSTALL_DIR}/storage/logs/worker.log
EOF

supervisorctl reread
supervisorctl update

log "INSTALL COMPLETE"
echo "URL: http://<server-ip>/"
echo "DB USER: ${DB_USER}"
echo "DB PASS: ${DB_PASS}"
echo "Log: ${LOG_FILE}"
