FROM php:8.3-apache

# Install PHP extensions required by LiteCart
RUN apt-get update && apt-get install -y \
	libfreetype6-dev \
	libjpeg62-turbo-dev \
	libpng-dev \
	libwebp-dev \
	libzip-dev \
	libicu-dev \
	libcurl4-openssl-dev \
	libxml2-dev \
	locales \
	&& sed -i '/en_US.UTF-8/s/^# //' /etc/locale.gen \
	&& sed -i '/de_DE.UTF-8/s/^# //' /etc/locale.gen \
	&& locale-gen \
	&& docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
	&& docker-php-ext-install -j$(nproc) \
		gd \
		mysqli \
		zip \
		intl \
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

# Set up storage directories
RUN mkdir -p /var/www/html/public_html/storage/cache \
		/var/www/html/public_html/storage/data \
		/var/www/html/public_html/storage/files \
		/var/www/html/public_html/storage/images \
		/var/www/html/public_html/storage/backups \
		/var/www/html/public_html/storage/logs \
		/var/www/html/public_html/storage/vmods/.cache \
	&& touch /var/www/html/public_html/storage/config.inc.php \
		/var/www/html/public_html/storage/.htaccess \
		/var/www/html/public_html/storage/robots.txt \
		/var/www/html/public_html/storage/vmods/.installed \
		/var/www/html/public_html/storage/vmods/.settings \
		/var/www/html/public_html/storage/vmods/.htaccess \
		/var/www/html/public_html/storage/vmods/.cache/.checked \
		/var/www/html/public_html/storage/vmods/.cache/.modifications

# Copy default storage data if available
RUN if [ -d /var/www/html/public_html/install/data/default/storage ]; then \
		cp -rn /var/www/html/public_html/install/data/default/storage/* /var/www/html/public_html/storage/; \
	fi

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html/public_html \
	&& chmod -R 755 /var/www/html/public_html \
	&& chmod -R 775 /var/www/html/public_html/storage

EXPOSE 80

CMD ["apache2-foreground"]
