FROM php:8.2-apache-bookworm

# Update debian & get required packages
RUN apt-get update && apt-get install -y \
    apt-transport-https \
    unzip \
    p7zip-full \
    libzip-dev \
    libpq-dev \
    libicu-dev \
    zlib1g-dev \
    g++ \
    libxml2-dev \
    curl \
    libonig-dev \
    && rm -rf /var/lib/apt/lists/*

# Set apache site
COPY ./resources/docker/apache.conf /etc/apache2/sites-available/apache_http.conf
RUN cd /etc/apache2/sites-available && a2dissite --quiet * \
    && a2enmod --quiet rewrite \
    && a2ensite --quiet apache_http.conf

# Use the default production php configuration
RUN mv "${PHP_INI_DIR}/php.ini-production" "${PHP_INI_DIR}/php.ini"

# Install PHP Extensions
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    zip \
    intl \
    bcmath \
    opcache \
    xml \
    dom \
    mbstring \
    pcntl

# Install APCu
RUN pecl install apcu && docker-php-ext-enable apcu

# PHP Configuration
RUN echo "expose_php = Off" >> "${PHP_INI_DIR}/php.ini" && \
    echo "memory_limit=256M" >> "${PHP_INI_DIR}/php.ini" && \
    echo "max_execution_time=30" >> "${PHP_INI_DIR}/php.ini" && \
    echo "post_max_size=50M" >> "${PHP_INI_DIR}/php.ini" && \
    echo "upload_max_filesize=50M" >> "${PHP_INI_DIR}/php.ini" && \
    echo "display_errors = Off" >> "${PHP_INI_DIR}/php.ini" && \
    echo "log_errors = On" >> "${PHP_INI_DIR}/php.ini"

# OPcache Configuration
RUN echo "opcache.enable=1" >> "${PHP_INI_DIR}/php.ini" && \
    echo "opcache.enable_cli=0" >> "${PHP_INI_DIR}/php.ini" && \
    echo "opcache.memory_consumption=192" >> "${PHP_INI_DIR}/php.ini" && \
    echo "opcache.interned_strings_buffer=16" >> "${PHP_INI_DIR}/php.ini" && \
    echo "opcache.max_accelerated_files=20000" >> "${PHP_INI_DIR}/php.ini" && \
    echo "opcache.validate_timestamps=0" >> "${PHP_INI_DIR}/php.ini" && \
    echo "opcache.revalidate_freq=0" >> "${PHP_INI_DIR}/php.ini" && \
    echo "opcache.max_wasted_percentage=10" >> "${PHP_INI_DIR}/php.ini" && \
    echo "opcache.fast_shutdown=1" >> "${PHP_INI_DIR}/php.ini"

# APCu Configuration
RUN echo "apc.enabled=1" >> "${PHP_INI_DIR}/php.ini" && \
    echo "apc.shm_size=64M" >> "${PHP_INI_DIR}/php.ini" && \
    echo "apc.ttl=3600" >> "${PHP_INI_DIR}/php.ini"

# Install Composer
RUN php -r "readfile('https://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

EXPOSE 80
WORKDIR /var/www/app
CMD ["apache2-foreground"]
