# Simple Cart Checkout / Webshop Application written in PHP with the Symfony framework

## Project Overview
This project involves the development of a shopping cart checkout system using Symfony/Workflow and ORM.

## Technologies
- PHP, Symfony \w workflow, ORM/Doctrine / SQLite
- npm / yarn / bun
- webpack / bootstrap / sass / babel

## Requirements
- Dockerized
- Implemented with PHP 8.1, should be also compatible with 8.0 and 8.2.
- Composer
- npm/yarn/bun

## Docker Container Build

docker build -t symfony-app .

## Docker Container Run

docker compose up -d

## Factory, Filling the DB

/shop/fill e.g.: `https://localhost:4444/shop/fill`

## Development:

`npm run watch`

## Known issues
- the first time you make a order, there is a state error and you get redirected to the front page. After that, it seems to work normal
- Checkout as a logged in user is not implemented yet

## Test Server:
https://checkout.unkonventionell.at/
