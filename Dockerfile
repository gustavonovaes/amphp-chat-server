FROM php:7.4-alpine

# Install composer
RUN wget -O- https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# Use the default production configuration
# RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Change max_execution_time to -1
# RUN sed -i -e "s/max_execution_time = [0-9]\+/max_execution_time = -1/g" /usr/local/etc/php/php.ini