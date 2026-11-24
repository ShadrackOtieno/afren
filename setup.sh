#!/usr/bin/env bash
# One-shot local setup. Prereqs: PHP 8.2+, Composer 2, MySQL 8 (or MariaDB 10.6+), database "afren" created.
set -e
[ -f .env ] || cp .env.example .env
composer install
php artisan key:generate
echo ">>> Edit .env (DB_USERNAME / DB_PASSWORD etc.) if needed, then press Enter"; read -r _
php artisan migrate --seed
php artisan storage:link 2>/dev/null || true
echo "Done. Run: php artisan serve   → http://localhost:8000   (admin: /admin/login)"
