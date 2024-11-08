FROM php:8.2-fpm

WORKDIR /var/www/cron-laravel
VOLUME [ "/var/www/con-laravel" ]

RUN docker-php-ext-install pdo pdo_mysql

RUN apt-get update && apt-get install -y \
    libwebp-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    unzip \
    curl \
    vim \
    git \
    libzip-dev \
    libpq-dev \
    libssl-dev  \
    libcurl4-openssl-dev \
    pkg-config \
    libxml2-dev \
    iputils-ping \
    supervisor \
    libpq-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Configure GD library
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
&&  docker-php-ext-install soap \
    gd \
    zip \
    pdo \
    pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer