FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first
COPY composer.json composer.lock* ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy rest of application files
COPY . .

# Regenerate autoloader after all files are copied
RUN composer dump-autoload --optimize --no-dev

# Create data directory with proper permissions
RUN mkdir -p /var/www/html/data && chmod -R 777 /var/www/html/data

# Expose port (Render will set the PORT env variable)
EXPOSE 8080

# Start PHP built-in server
CMD php -S 0.0.0.0:${PORT:-8080} -t public