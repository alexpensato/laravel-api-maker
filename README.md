# Laravel Api Maker

*Automate the generation of your REST APIs with the console generator command make:api*

## Introduction

This package is an extended version of the [laravel-api-generator](https://github.com/arrilot/laravel-api-generator) package.
It includes a completely rewritten BaseController, now renamed to **ApiController**. This package also presents two new controller types: 
**ReadOnlyController**, for APIs that don't need writing capabilities; and **WebController**, 
for applications that need support for frontend scaffolding views.

This package relies on the **Repository Design Pattern**, which means that *Controllers* will access *Models* through a 
*Repository* interface, which provides better separation of concerns for data access methods and business rules.
This repository implementation was inspired by [Connor Leech](https://medium.com/employbl/use-the-repository-design-pattern-in-a-laravel-application-13f0b46a3dce)'s 
and [Jeff Decena](https://medium.com/@jsdecena/refactor-the-simple-tdd-in-laravel-a92dd48f2cdd)'s articles.

This package also uses [Codeception/Specify](https://github.com/Codeception/Specify) and [Codeception/Verify](https://github.com/Codeception/Verify) packages 
to get you started with BDD-style unit testing.

This enhanced version of the **console generator** creates the following files for each Model in **one single command**:

1. ApiController extended class
 
2. Fractal Transformer class
 
3. Repository interface 
 
4. Repository implementation class

5. Unit test file configured for BDD

It also modifies the following configuration files:

1. Adds routes to **routes/api.php**

2. Adds repository binding to **ApiServiceProvider**

This package was designed to get you started with professional REST API best practices.

## Compatibility

Laravel API Maker  | Laravel
------------------ | ----------------
1.0.x              | 5.6
1.1.x              | 5.7


## Installation

*Step 1* - Run ```composer require alexpensato/laravel-api-maker```

*Step 2* - Copy the `ApiServiceProvider` class to `app/Providers` folder:

  ```cp -R vendor/alexpensato/laravel-api-maker/templates/Providers app/Providers``` 

  and check what you got there.

*Step 3* - Register the service providers in the `config/app.php` configuration file

```php
<?php

'providers' => [
    
    ...
    
    /*
     * Package Service Providers...
     */
    
    Pensato\Api\ServiceProvider::class,
    
    /*
     * Application Service Providers...
     */
    
    App\Providers\ApiServiceProvider::class,
],
?>
```

*Step 4* - Laravel already provides an API routes file. To correctly configure the console generator automation process,
you need to choose one of the routing templates presented in `vendor/alexpensato/laravel-api-maker/templates/routes/api.php`, 
and then copy it to your project's api routing file. 

For instance, copy the code below to the end of your `routes/api.php` project file.

```php
<?php

Route::group(['prefix' => 'v1'], function () {
    //
});
```

It will allow the console generator to automatically inject resources routes to the api routing file.

## Usage

### Generator

The only console command that is added and needed is ```artisan make:api <ModelName>```.

Imagine you need to create a rest api to list/create/update etc. users from users table.
To achieve that you need to do lots of boilerplate operations - create controller, transformer, repository, 
unit testing, set up needed routes and configuring repository binding.

```php artisan make:api User``` does all the work for you.

It is important to notice that this command assumes that *Model* has already been created in the `Models` folder.

For instance, you can create a Model using the following command:

```php artisan make:model -mf Models/<ModelName>```

### Conventions

You may have noticed that the `Controller` which has just been generated includes two public methods: `__constructor()` and `transformer()`.
That's because those methods are the only thing that you need in your controller to set up a basic REST API.

The list of routes that are available out-of-the-box:

1. `GET api/v1/users`
2. `GET api/v1/users/{id}`
3. `POST  api/v1/users`
4. `PUT api/v1/users/{id}`
5. `DELETE  api/v1/users/{id}`

Request and response formats are JSON. Response format is defined by [Fractal](https://github.com/thephpleague/fractal).

Fractal includes are supported via `$_GET['include']`.

Validation rules for create and update can be set by overwriting `rulesForCreate` and `rulesForUpdate` in your controller.

## Contributions

…are always welcome.
Don’t hesitate to submit a [bug report](https://github.com/alexpensato/laravel-api-maker/issues) or 
a [pull request](https://github.com/alexpensato/laravel-api-maker/pulls).

When filling a bug report or submitting a new feature, please try including supporting test cases.


## License

This project is licensed under [MIT](https://opensource.org/licenses/MIT) license.

Although it is a tested package, use at your risk!