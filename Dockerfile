# ============================================================
# WorkForce Pro – Production Dockerfile
# PHP 8.3 + Apache with security hardening
# ============================================================
FROM php:8.3-apache

# ── System packages ─────────────────────────────────────────
RUN apt-get update && apt-get install -y --no-install-recommends \
        libpng-dev \
        libjpeg-dev \
        libwebp-dev \
        libzip-dev \
        zip \
        unzip \
        curl \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install pdo pdo_mysql gd zip opcache \
    && a2enmod rewrite headers expires deflate \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# ── PHP production ini ────────────────────────────────────────
COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini

# ── Apache virtual host config ────────────────────────────────
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# ── Copy application source ───────────────────────────────────
COPY . /var/www/html/

# ── Remove dev/local-only files from image ────────────────────
RUN rm -rf /var/www/html/docker \
           /var/www/html/docker-compose.yml \
           /var/www/html/.env.example \
           /var/www/html/.gitignore \
           /var/www/html/database/backups/*.sql 2>/dev/null || true

# ── Directory permissions ─────────────────────────────────────
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/uploads \
    && chmod -R 775 /var/www/html/logs \
    && chmod -R 775 /var/www/html/database/backups

# ── Entrypoint ────────────────────────────────────────────────
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
CMD ["apache2-foreground"]
