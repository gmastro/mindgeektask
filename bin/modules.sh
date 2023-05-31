#!/bin/bash

# try to upgrade to latest version of composer
composer self-update

# add react support and install it
composer require laravel/breeze --dev
php artisan breeze:install react

# start react
npm run dev

# initial migration for all breeze content and tables
php artisan migrate