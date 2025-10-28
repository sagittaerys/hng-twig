# Use the official PHP 8.2 image with Apache
FROM php:8.2-apache

# Enable Apache mod_rewrite for clean URLs
RUN a2enmod rewrite

# Set the working directory inside the container
WORKDIR /var/www/html

# Copy all files from your project into the container
COPY . .

# Expose the web port
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
