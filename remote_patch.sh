#!/bin/bash

# AdmixCentral Remote Fix Script
# Run this on the remote server (172.30.1.102) to fix the Logo Upload/403 Issue.

PROJECT_DIR="/home/baga/Code/admixcentral"
CONTROLLER="$PROJECT_DIR/app/Http/Controllers/SystemCustomizationController.php"
SERVER_PHP="$PROJECT_DIR/server.php"

echo "Applying fixes to $PROJECT_DIR..."

# 1. Fix SystemCustomizationController.php (Use 'public' disk)
if [ -f "$CONTROLLER" ]; then
    echo "Patching Controller..."
    sed -i "s/store('public\/customization')/store('customization', 'public')/g" "$CONTROLLER"
    echo "Controller patched."
else
    echo "Error: Controller not found at $CONTROLLER"
fi

# 2. Restore server.php if missing
if [ ! -f "$SERVER_PHP" ]; then
    echo "Restoring server.php..."
    cat <<EOF > "$SERVER_PHP"
<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylor@laravel.com>
 */

\$uri = urldecode(
    parse_url(\$_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

if (\$uri !== '/' && file_exists(__DIR__.'/public'.\$uri)) {
    return false;
}

require_once __DIR__.'/public/index.php';
EOF
    echo "server.php restored."
fi

# 3. Fix Storage Link
cd "$PROJECT_DIR" || exit
echo "Fixing Storage Link..."
rm -rf public/storage
php artisan storage:link

# 4. Fix Permissions
echo "Fixing Permissions..."
chmod -R 775 storage bootstrap/cache
chmod -R 775 storage/app/public

# 5. Clear Caches
echo "Clearing Caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "---------------------------------------------------"
echo "Fix Complete!"
echo "Please restart your development server:"
echo "php artisan serve --host=0.0.0.0 --port=8000"
echo "Then try uploading your logo again."
