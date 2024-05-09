FROM php:apache-bookworm

COPY .docker/apache2.conf /etc/apache2/sites-available/000-default.conf
COPY . /var/www/mockrr

# Install composer
RUN curl -sS https://getcomposer.org/installer | \
    php -- --install-dir=/usr/local/bin --filename=composer

# Install mockrr and dependencies
RUN chmod +x /usr/local/bin/composer && \
    cd /var/www/mockrr && \
    composer install

ENV MOCKRR_APP_ROOT /var/www/mockrr

