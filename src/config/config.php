<?php

return [

    /*
     * Relative path from the app directory to api controllers directory.
     */
    'repointerface_dir'  => 'Repositories',

    /*
     * Relative path from the app directory to api controllers directory.
     */
    'repository_dir'  => 'Repositories',

    /*
     * Relative path from the app directory to api controllers directory.
     */
    'controller_dir'  => 'Http\\Controllers',

    /*
     * Relative path from the app directory to transformers directory.
     */
    'transformer_dir' => 'Http\\Transformers',

    /*
     * Relative path from the tests directory to the bdd directory.
     */
    'bdd_dir' => 'tests\\Feature',

    /*
     * Relative path from the app directory to the api routes file.
     */
    'routes_file'      => 'Http\\routes.php',

    /*
     * Relative path from the app directory to the api service provider file.
     */
    'provider_file'      => 'Providers/ApiServiceProvider.php',

    /*
     * Relative path from the app directory to the models directory. Typically it's either 'Models' or ''.
     */
    'models_base_dir'  => 'Models',

    /*
     * Relative path from the base directory to the api repository interface stub.
     */
    'repointerface_stub'  => 'vendor/alexpensato/laravel-api-maker/src/Generator/stubs/repositoryInterface.stube.stub',

    /*
     * Relative path from the base directory to the api repository class stub.
     */
    'repository_stub'  => 'vendor/alexpensato/laravel-api-maker/src/Generator/stubs/repository.stub',

    /*
     * Relative path from the base directory to the api controller stub.
     */
    'controller_stub'  => 'vendor/alexpensato/laravel-api-maker/src/Generator/stubs/controller.stub',

    /*
     * Relative path from the base directory to the route stub.
     */
    'route_stub'       => 'vendor/alexpensato/laravel-api-maker/src/Generator/stubs/route.stub',

    /*
     * Relative path from the base directory to the provider stub.
     */
    'provider_stub'       => 'vendor/alexpensato/laravel-api-maker/src/Generator/stubs/provider.stub',

    /*
     * Relative path from the base directory to the transformer stub.
     */
    'transformer_stub' => 'vendor/arrilot/laravel-api-generator/src/Generator/stubs/transformer.stub',

    /*
     * Relative path from the base directory to the transformer stub.
     */
    'bdd_stub' => 'vendor/arrilot/laravel-api-generator/src/Generator/stubs/bdd.stub',
];
