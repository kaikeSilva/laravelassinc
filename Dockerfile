FROM php:8.2-cli

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip && \
    pecl install redis && \
    docker-php-ext-enable redis && \
    docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Increase PHP memory limit
RUN echo "memory_limit = 512M" > /usr/local/etc/php/conf.d/memory-limit.ini

# Create application user
RUN useradd -G www-data,root -u 1000 -d /home/laravel laravel && \
    mkdir -p /home/laravel/.composer && \
    chown -R laravel:laravel /home/laravel

# Set working directory
WORKDIR /var/www

# Copy project files
COPY . .

# Set permissions
RUN chown -R laravel:laravel /var/www

# Switch to the laravel user
USER laravel

# Install Composer dependencies
RUN composer install

# Expose port 8000 for PHP's built-in server
EXPOSE 8000

# Command to run the PHP built-in server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]