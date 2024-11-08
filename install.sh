cp .env.example .env
composer install
php artisan storage:link
chmod -R 777 storage/
php artisan migrate
php artisan key:generate