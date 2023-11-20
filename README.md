# Basic Laravel Rest API

This is an example of a REST API using auth tokens with Laravel Sanctum

## How to Run
- Change the *.env.example* to *.env* and add your database info
- Install Package -  `composer install`
- Run Migration - `php artisan migrate`
- Run Seeder - `php artisan db:seed`
- Run Server - `php artisan serve`
- Happy Coding 👊

## Routes

```
# Public

GET   /api/products
GET   /api/products/:id

POST   /api/login
@body: email, password

POST   /api/register
@body: name, email, password, password_confirmation


# Protected

POST   /api/products
@body: name, slug, description, price

PUT   /api/products/:id
@body: ?name, ?slug, ?description, ?price

DELETE  /api/products/:id

POST    /api/logout
```
