FROM php:8.3-apache
RUN a2enmod rewrite \
   && docker-php-ext-install pdo pdo_mysql \
   && docker-php-ext-enable pdo pdo_mysql \