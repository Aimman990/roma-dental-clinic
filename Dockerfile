FROM php:8.2-apache

# تثبيت الإضافات اللازمة لـ PostgreSQL و Composer
RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql

# تفعيل خاصية الروابط في Apache
RUN a2enmod rewrite

# ضبط مجلد العمل داخل السيرفر
WORKDIR /var/www/html
COPY . .

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# ضبط صلاحيات المجلدات لتجنب أخطاء Permission
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# توجيه Apache لمجلد public الخاص بلارافل
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

EXPOSE 80