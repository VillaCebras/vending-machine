FROM php:8.5-fpm

# Install system dependencies and PHP extensions
RUN apt-get update \
	&& apt-get install -y --no-install-recommends \
		git \
		unzip \
		zlib1g-dev \
		libzip-dev \
	&& docker-php-ext-install pdo_mysql zip \
	&& rm -rf /var/lib/apt/lists/*

# Copy Composer from the official Composer image
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
RUN chmod +x /usr/local/bin/composer

EXPOSE 9000
CMD ["php-fpm"]

