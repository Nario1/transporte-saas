# ── ETAPA 1: Compilar assets de frontend ──
FROM node:20-alpine AS frontend-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# ── ETAPA 2: Aplicación PHP + Nginx ──
FROM php:8.2-fpm-alpine

# Instalar dependencias del sistema y extensiones PHP
RUN apk add --no-cache nginx git unzip supervisor libpng-dev libzip-dev zip freetype-dev libjpeg-turbo-dev libwebp-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo_mysql gd zip bcmath pcntl

# Directorio de trabajo
WORKDIR /var/www

# Copiar el código del proyecto
COPY . .

# Copiar los assets compilados de la Etapa 1
COPY --from=frontend-builder /app/public/build ./public/build

# Instalar Composer y dependencias PHP sin dependencias de desarrollo
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-dev --optimize-autoloader

# Crear directorios para logs de supervisor y ajustar permisos
RUN mkdir -p /var/log/supervisor /var/run \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Copiar configuraciones de Docker
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisor.conf /etc/supervisor/conf.d/supervisord.conf

# Exponer el puerto de Nginx
EXPOSE 80

# Comando para arrancar supervisor, que levantará nginx, php-fpm, queue:work y reverb:start
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
