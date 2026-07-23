# PHP 8.4 FPM on Debian (not Alpine): the Tailwind bundle downloads a glibc
# standalone binary, so `tailwind:build` only works here.
FROM php:8.4-fpm

# gd  -> the photo upload validates images with detectCorrupted
# intl -> symfony/intl
# pdo_mysql -> Doctrine
# zip + git + unzip -> Composer
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" gd intl opcache pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY php.ini /usr/local/etc/php/conf.d/app.ini
COPY entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

WORKDIR /var/www/html

# The source is bind-mounted by compose, so nothing is copied into the image.
ENTRYPOINT ["entrypoint"]
CMD ["php-fpm"]
