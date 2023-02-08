FROM php:8.2-fpm

ENV DEBIAN_FRONTEND noninteractive


RUN apt-get update && \
  apt-get install -y --no-install-recommends apt-utils nano sudo unzip git && \
  rm -r /var/lib/apt/lists/*

# Xdebug
RUN pecl install xdebug && \
  docker-php-ext-enable xdebug

# pdo_mysql
RUN docker-php-ext-install pdo_mysql

RUN apt-get update && \
  apt-get install -y --no-install-recommends libxml2-dev && \
  docker-php-ext-install xml && \
  rm -r /var/lib/apt/lists/*

# intl
RUN apt-get update && \
  apt-get install -y --no-install-recommends libicu-dev && \
  docker-php-ext-install intl  && \
  rm -r /var/lib/apt/lists/*

# xsl
RUN apt-get update && \
  apt-get install -y --no-install-recommends libxslt-dev && \
  docker-php-ext-install xsl && \
  rm -r /var/lib/apt/lists/*

# gd
RUN apt-get update && \
  apt-get install -y --no-install-recommends libfreetype6-dev libjpeg62-turbo-dev libpng-dev libwebp-dev && \
  docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp && \
  docker-php-ext-install -j$(nproc) gd && \
  rm -r /var/lib/apt/lists/*

# zip
RUN apt-get update && \
  apt-get install -y --no-install-recommends libzip-dev && \
  docker-php-ext-install zip && \
  rm -r /var/lib/apt/lists/*

RUN docker-php-ext-install calendar

RUN docker-php-ext-install pcntl

RUN docker-php-ext-install exif

RUN apt-get update && \
  apt-get install -y --no-install-recommends libmagickwand-dev && \
  pecl install imagick -y && \
  docker-php-ext-enable imagick && \
  rm -r /var/lib/apt/lists/* \
