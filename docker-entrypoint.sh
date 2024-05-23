#!/bin/bash

# Install Composer packages
composer install

# Keep the container running
tail -f /dev/null
