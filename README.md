# Laravel Api Maker

*Automate the generation of your REST APIs: console generator, API and Repository skeletons, Fractal transformations and BDD-style specification files*

## Introduction

This package is originally forked from [laravel-api-generator](https://github.com/arrilot/laravel-api-generator), but includes many features not present in the original, like repository base classes and generated classes, and BDD-style unit test files.

Features:

1. Console generator which creates Controller, Fractal Transformer, Repository, routes and BDD-style unit test files in a single command.

2. Basic REST API skeleton that can be really helpful if you need something standard.

3. Repository Design Pattern base classes.

If you do not use Fractal for your transformation layer, and do not use the Repository Design Pattern with your Models, and don't need BDD-style unit test files, this package is probably not the right choice for you.

## Installation

1) Run ```composer require alexpensato/laravel-api-maker```

2) Register a service provider in the `app.php` configuration file

```php
<?php

'providers' => [
    ...
    'Pensato\Api\ServiceProvider',
],
?>
```

3) Copy basic folder structure to app/Api ```cp -R vendor/alexpensato/laravel-api-maker/templates/Providers app/Providers``` and check what you got there.
If you need you can use different paths later.


## Usage

### Generator

The only console command that is added is ```artisan make:api <ModelName>```.

Imagine you need to create a rest api to list/create/update etc users from users table.
To achieve that you need to do lots of boilerplate operations - create controller, transformer, set up needed routes.

```php artisan make:api User``` does all the work for you.

