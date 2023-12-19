# Simple Cart Checkout / Webshop Application written in PHP with the Symfony framework

## Project Overview
This project involves the development of a shopping cart checkout system using Symfony/Workflow and ORM.

## Technologies
- PHP, Symfony \w workflow, ORM/Doctrine / SQLite
- npm / yarn / bun
- webpack / bootstrap / sass / babel

## Requirements
- Currently **not** dockerized
- Implemented with PHP 8.1, should be also compatible with 8.0 and 8.2.
- Composer
- npm/yarn/bun

## Deployment

`composer install`

`php bin/console doctrine:database:create`

`php bin/console doctrine:migrations:migrate`

Depending on your setup you meight need a `.htaccess` file in the public directory:

```
  RewriteEngine On
  RewriteBase /
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule ^ index.php [QSA,L]
```

After wards you can prefil the DB with with path:
/shop/fill e.g.: `https://localhost:8000/shop/fill`

For local development:

`symfony server:start`

`npm run watch`


## Known issues
- the first time you make a order, there is a state error and you get redirected to the front page. After that, it seems to work normal
- Checkout as a logged in user is not implemented yet

## Test Server:
https://checkout.unkonventionell.at/
