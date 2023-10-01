#!/bin/sh


php composer_2.phar dump-autoload;
php artisan config:cache;
php artisan migrate;
chmod -R 777 storage/logs/;
npm run prod;

# update application cache
php artisan optimize;

# start the application

php-fpm;
