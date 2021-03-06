<?php

return [

    /*
     * Relative path from the app directory to api controllers directory.
     */
    'repositoryInterface_dir'  => 'Repositories',

    /*
     * Relative path from the app directory to api controllers directory.
     */
    'repository_dir'  => 'Repositories',

    /*
     * Relative path from the app directory to api controllers directory.
     */
    'controller_dir'  => 'Http/Controllers/Api',

    /*
     * Relative path from the app directory to transformers directory.
     */
    'transformer_dir' => 'Http/Transformers',

    /*
     * Relative path from the base directory to the tests directory.
     */
    'test_dir' => 'tests/Feature',

    /*
     * Relative path from the app directory to the api routes file.
     */
    'routes_file'      => 'routes/api.php',

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
    'repositoryInterface_stub'  => 'vendor/alexpensato/laravel-api-maker/src/Generator/stubs/repositoryInterface.stub',

    /*
     * Relative path from the base directory to the api repository class stub.
     */
    'repository_stub'  => 'vendor/alexpensato/laravel-api-maker/src/Generator/stubs/repository.stub',

    /*
     * Relative path from the base directory to the api controller stub.
     */
    'controller_stub'  => 'vendor/alexpensato/laravel-api-maker/src/Generator/stubs/controller.stub',

    /*
     * Relative path from the base directory to the api controller stub.
     */
    'controller_readonly_stub'  => 'vendor/alexpensato/laravel-api-maker/src/Generator/stubs/controller_readonly.stub',

    /*
     * Relative path from the base directory to the route stub.
     */
    'route_stub'       => 'vendor/alexpensato/laravel-api-maker/src/Generator/stubs/route.stub',

    /*
     * Relative path from the base directory to the count route stub.
     */
    'count_stub'       => 'vendor/alexpensato/laravel-api-maker/src/Generator/stubs/count.stub',

    /*
     * Relative path from the base directory to the associate route stub.
     */
    'associate_stub'       => 'vendor/alexpensato/laravel-api-maker/src/Generator/stubs/associate.stub',

    /*
     * Relative path from the base directory to the provider stub.
     */
    'provider_stub'       => 'vendor/alexpensato/laravel-api-maker/src/Generator/stubs/provider.stub',

    /*
     * Relative path from the base directory to the transformer stub.
     */
    'transformer_stub' => 'vendor/alexpensato/laravel-api-maker/src/Generator/stubs/transformer.stub',

    /*
     * Relative path from the base directory to the test stub.
     */
    'test_stub' => 'vendor/alexpensato/laravel-api-maker/src/Generator/stubs/test.stub',

    /*
     * Relative path from the base directory to the read only test stub.
     */
    'test_readonly_stub' => 'vendor/alexpensato/laravel-api-maker/src/Generator/stubs/test_readonly.stub',
];
