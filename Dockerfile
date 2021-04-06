FROM php:7.4-fpm

RUN apt update && apt install -y libgmp-dev nginx git
RUN docker-php-ext-install gmp
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
ADD ./nginx.conf /etc/nginx/sites-available/default
ADD ./www /var/www
RUN cd /var/www && composer install
ADD ./entrypoint.sh /etc

ENTRYPOINT [ "/etc/entrypoint.sh" ]

WORKDIR /var/www