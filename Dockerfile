FROM php:8.3-apache

# Install PHP extensions required by LiteCart
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    libicu-dev \
    libcurl4-openssl-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd \
        mysqli \
        pdo_mysql \
        zip \
        intl \
        curl \
        opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite headers expires

# Set recommended PHP settings for LiteCart
RUN { \
    echo 'upload_max_filesize = 50M'; \
    echo 'post_max_size = 50M'; \
    echo 'memory_limit = 256M'; \
    echo 'max_execution_time = 300'; \
    echo 'session.save_path = /tmp'; \
    echo 'date.timezone = UTC'; \
    echo 'expose_php = Off'; \
} > /usr/local/etc/php/conf.d/litecart.ini

# Set Apache document root to public_html
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public_html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Allow .htaccess overrides
RUN sed -ri -e 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Copy application files
COPY public_html/ /var/www/html/public_html/

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html/public_html \
    && chmod -R 755 /var/www/html/public_html \
    && chmod -R 775 /var/www/html/public_html/cache \
    && chmod -R 775 /var/www/html/public_html/data \
    && chmod -R 775 /var/www/html/public_html/images \
    && chmod -R 775 /var/www/html/public_html/logs \
    && chmod -R 755 /var/www/html/public_html/vmods

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
