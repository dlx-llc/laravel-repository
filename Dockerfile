# Define base image
FROM php:8.5-fpm-alpine

# Install alpine packages
RUN apk update && apk add --no-cache bash

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set bash as the default shell
SHELL ["/bin/bash", "-c"]

# Set workdir and copy project files
WORKDIR /home/larepo/app
COPY . .

# Make sure startup script is executable
RUN chmod +x docker-entrypoint.sh

# Define startup command
ENTRYPOINT /home/larepo/app/docker-entrypoint.sh
