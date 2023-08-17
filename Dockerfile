# Use the official PHP image as the base image
FROM php:8.0-fpm

# Set the working directory inside the container
WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Copy your application files to the container
COPY . .

# Expose the port the application runs on
EXPOSE 9000

# Start the PHP-FPM server
CMD ["php-fpm"]
