FROM php:8.4-fpm

# Install Nginx, OpenSSL, and dependencies
RUN apt-get update && apt-get install -y \
    nginx \
    openssl \
    curl \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    zlib1g-dev \
    libzip-dev \
    libpq-dev \
    libmagickwand-dev \
    libheif-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install and configure PHP extensions
RUN docker-php-source extract \
    && cd /usr/src/php/ext/gd \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd exif pdo_pgsql pgsql \
    && docker-php-source delete

# Configure PHP extensions
RUN echo "extension=gd.so" > /usr/local/etc/php/conf.d/docker-php-ext-gd.ini \
    && echo "extension=exif.so" > /usr/local/etc/php/conf.d/docker-php-ext-exif.ini \
    && echo "extension=pdo_pgsql.so" > /usr/local/etc/php/conf.d/docker-php-ext-pdo_pgsql.ini \
    && echo "extension=pgsql.so" > /usr/local/etc/php/conf.d/docker-php-ext-pgsql.ini \
    && echo "extension=imagick.so" > /usr/local/etc/php/conf.d/docker-php-ext-imagick.ini

# Configure PHP-FPM to use Unix socket
RUN sed -i 's/listen = 127.0.0.1:9000/listen = \/var\/run\/php-fpm.sock/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/;listen.owner = www-data/listen.owner = www-data/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/;listen.group = www-data/listen.group = www-data/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/;listen.mode = 0660/listen.mode = 0660/' /usr/local/etc/php-fpm.d/www.conf

# Create socket directory
RUN mkdir -p /var/run/php/

# Copy custom configs
COPY /dev/php-fpm/php-fpm.conf /usr/local/etc/php-fpm.conf
COPY /dev/nginx/nginx.conf /etc/nginx/nginx.conf

# Add Nginx user to PHP socket group
RUN usermod -a -G www-data nginx || true


# Set working directory
WORKDIR /var/www/webroot

# Expose HTTP and HTTPS ports
EXPOSE 80 443

# Create a startup script
RUN echo "#!/bin/bash\n\
echo 'Checking PHP extensions...'\n\
php -m | grep gd\n\
php -m | grep exif\n\
php-fpm -D\n\
nginx -g 'daemon off;'" > /usr/local/bin/start-services.sh && \
    chmod +x /usr/local/bin/start-services.sh

# Start services
CMD ["/usr/local/bin/start-services.sh"]
