#
# Usage:
#   docker build --build-arg MYSQL_ROOT_PASSWORD=yourpassword -t litecart .
#

FROM debian:latest

# Set timezone
ENV TZ=Europe/London
RUN ln -fs /usr/share/zoneinfo/$TZ /etc/localtime \
 && echo $TZ > /etc/timezone

# Update system and install required packages
RUN apt update && apt full-upgrade -y \
 && apt install -y curl nano unzip apache2 libapache2-mod-php mariadb-server \
   php php-common php-cli php-fpm php-apcu php-curl php-dom php-gd \
   php-imagick php-mysql php-simplexml php-mbstring php-intl php-zip php-xml \
 && apt clean

# Install additional locales
#apt install -y language-pack-es language-pack-fr language-pack-de

# Enable Apache modules
RUN a2enmod proxy_fcgi rewrite headers setenvif ssl

# Configure PHP-FPM and PHP settings
RUN PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;") && \
    echo "Detected PHP version: $PHP_VERSION" && \
    ls -la /etc/php/ && \
    ls -la /etc/php/$PHP_VERSION/ && \
    a2enconf "php${PHP_VERSION}-fpm" && \
    sed -ri "s/;?memory_limit\s*=\s*[^\s]*/memory_limit = 256M/" "/etc/php/${PHP_VERSION}/apache2/php.ini" && \
    sed -ri "s/;?upload_max_filesize\s*=\s*[^\s]*/upload_max_filesize = 64M/" "/etc/php/${PHP_VERSION}/apache2/php.ini" && \
    sed -ri "s/;?date\.timezone\s*=\s*[^\s]*/date.timezone = Europe\/London/g" "/etc/php/${PHP_VERSION}/apache2/php.ini"

# Set MariaDB root password via environment variable
ARG MYSQL_ROOT_PASSWORD
ENV MYSQL_ROOT_PASSWORD=${MYSQL_ROOT_PASSWORD}

# Configure and secure MariaDB
RUN service mariadb start \
 && sleep 3 \
 && mariadb-admin -u root password '${MYSQL_ROOT_PASSWORD}' \
 && mariadb -u root -p${MYSQL_ROOT_PASSWORD} -e "DELETE FROM mysql.user WHERE User='';" \
 && mariadb -u root -p${MYSQL_ROOT_PASSWORD} -e "DROP DATABASE IF EXISTS test;" \
 && mariadb -u root -p${MYSQL_ROOT_PASSWORD} -e "FLUSH PRIVILEGES;" \
 && service mariadb stop

# Expose TCP ports for SSH, HTTP/HTTPS, and MySQL/MariaDB
EXPOSE 22 80 443 3306

# Download LiteCart installer
RUN cd /var/www/html && \
  curl -O https://raw.githubusercontent.com/litecart/installer/master/web/index.php

# Change owner of LiteCart files
RUN chown -R www-data:www-data /var/www/html

# Create startup script
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
# Start MariaDB\n\
service mariadb start\n\
\n\
# Start PHP-FPM (find the version directory)\n\
PHP_VERSION=$(ls /etc/php/ | grep -E "^[0-9]+\.[0-9]+$" | head -n 1)\n\
service "php${PHP_VERSION}-fpm" start\n\
\n\
# Start Apache in foreground\n\
apache2ctl -D FOREGROUND\n\
' > /start.sh && chmod +x /start.sh

# Set the startup script as the default command
CMD ["/start.sh"]
