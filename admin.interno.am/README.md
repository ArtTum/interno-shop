doctor911.am ERP run

Requirements for server: nginx (We need remove apache completely), php fpm, composer, redis, node, npm, git, mysql, supervisor.
Requirements for local: local server with php, composer, redis, node, npm, git.

Versions: (php: 8.2, node: 20.15.1, npm: 10.8.2).

Add files .env and laravel-echo-server.json to project core path.

Create DB 'vendors' on your server or local.

Needed commands at project core path for run manually:
composer i
npm i
php artisan key:generate
php artisan optimize
php artisan migrate --path=/database/migrations/0001_01_01_00000_create_all_countries_table.php
php artisan migrate --path=/database/migrations/0001_01_01_000001_create_vendors_table.php
php artisan migrate --path=/database/migrations/2024_09_24_110949_create_vendor_countries_table.php
php artisan migrate --path=/database/migrations/2024_10_16_104637_create_outlook_settings_table.php
php artisan prepare:base-vendor
php artisan prepare:db-for-vendor (Instead of php artisan migrate)

At supervisor or console:
laravel-echo-server start
php artisan queue:work (8 workers).
