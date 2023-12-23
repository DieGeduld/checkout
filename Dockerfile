# Schritt 1: Basis-Image verwenden
FROM php:8.1-fpm

# Setzen des Arbeitsverzeichnisses
WORKDIR /var/www

RUN apt update
RUN apt upgrade -y

# Installieren der benötigten PHP-Erweiterungen und anderer Abhängigkeiten
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Composer installieren
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Symfony-App kopieren
COPY . /var/www

# Berechtigungen setzen
RUN chown -R www-data:www-data /var/www

# Composer-Abhängigkeiten installieren
RUN composer install --no-interaction

RUN php bin/console doctrine:database:create

RUN php bin/console doctrine:migrations:migrate --no-interaction

RUN npm install

RUN npm run build

# Port freigeben
EXPOSE 4444

# Schritt 10: PHP-FPM starten
CMD ["php-fpm"]
