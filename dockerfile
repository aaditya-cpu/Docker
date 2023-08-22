# Use the latest WordPress image as the base
FROM wordpress:latest

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Change the working directory to WordPress root
WORKDIR /var/www/html

# Copy the composer.json file into the container
COPY ./composer.json /var/www/html/composer.json

# Run composer install to install the dependencies
RUN composer install
