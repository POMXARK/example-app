FROM php:8.1-apache

ENV PGHOST localhost
ENV POSTGRES_DB pgsql
ENV POSTGRES_USER postgres
ENV POSTGRES_PASSWORD 12345678

ENV DB_CONNECTION=pgsql
ENV DB_HOST=localhost
ENV DB_PORT=5432
ENV DB_DATABASE=example_app
ENV DB_USERNAME=postgres
ENV DB_PASSWORD=12345678

COPY . /var/www/html/public

ENV APACHE_DOCUMENT_ROOT ${APACHE_DOCUMENT_ROOT:-/var/www/html/public}

# Required for zip; php zip extension; png; node; vim; gd; gd; php mbstring extension; cron;
RUN apt-get update && \
    apt-get install -y zip libzip-dev libpng-dev gnupg vim libfreetype6-dev libjpeg62-turbo-dev libonig-dev  sudo postgresql postgresql-contrib - &&\
    apt-get install -y --no-install-recommends nodejs npm libssl-dev zlib1g-dev curl git unzip libxml2-dev libpq-dev libzip-dev && \
    pecl install apcu && \
    docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql && \
    docker-php-ext-install -j$(nproc) zip opcache intl pdo_pgsql pgsql && \
    docker-php-ext-enable apcu pdo_pgsql sodium && \
    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY pg_hba.conf /etc/postgresql/15/main/

#Installing node 20.x
RUN curl -sL https://deb.nodesource.com/setup_20.x| bash -
RUN apt-get install -y nodejs

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy custom apache virtual host configuration into container
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# Copy start stript into container
COPY start.sh /usr/local/bin/start

# Set apache folder permission
RUN chown -R www-data:www-data /var/www

# Activate Apache mod_rewrite
RUN a2enmod rewrite

# Set start script permission
RUN chmod u+x /usr/local/bin/start

# Cleanup
RUN apt-get clean
RUN apt-get autoclean

EXPOSE 80 5432

WORKDIR /var/www/html/public
#
CMD ["/usr/local/bin/start"]
