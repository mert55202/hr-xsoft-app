FROM php:8.4-cli

RUN docker-php-ext-install pdo_mysql

WORKDIR /var/www/html

EXPOSE 8000

CMD ["sh", "-c", "cd /var/www/html/public && exec php -S 0.0.0.0:8000 -t /var/www/html/public /var/www/html/vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php"]
