# Stage 1: Build Frontend Assets
FROM node:20-alpine AS node-builder
WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY . .

# Stage 2: PHP Application Environment
FROM php:8.3-fpm-alpine

WORKDIR /var/www/html

# Install system dependencies & PHP extensions
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    git \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    sqlite-dev \
    oniguruma-dev \
    nodejs \
    npm

RUN docker-php-ext-install \
    pdo \
    pdo_sqlite \
    mbstring \
    xml \
    bcmath \
    zip \
    pcntl \
    opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . /var/www/html

# Install Composer dependencies (optimizing autoloader for production)
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Copy node modules from node-builder to run build with PHP available for Wayfinder
COPY --from=node-builder /app/node_modules /var/www/html/node_modules
RUN npm run build

# Remove node_modules after build to keep image lean
RUN rm -rf /var/www/html/node_modules

# Configure Nginx & Supervisord
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Set directory permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
