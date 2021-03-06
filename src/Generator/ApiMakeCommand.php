<?php

namespace Pensato\Api\Generator;

use Illuminate\Console\DetectsApplicationNamespace;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;

class ApiMakeCommand extends Command
{
    use DetectsApplicationNamespace;

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:api {modelName} {--readonly}';

    /**
     * The console command name.
     *
     * @var string
     */
//    protected $name = 'make:api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create api controller, transformer, repository, routes and BDD-style unit tests for a given model (alexpensato/laravel-api-maker)';


    /**
     * Is this API read only or not?
     *
     * @var bool
     */
    protected $readOnly = false;
    /**
     * The array of variables available in stubs.
     *
     * @var array
     */
    protected $stubVariables = [
        'app'         => [],
        'model'       => [],
        'controller'  => [],
        'transformer' => [],
        'route'       => [],
        'repository' => [],
        'repositoryInterface' => [],
        'test' => [],
    ];

    protected $modelsBaseNamespace;

    /**
     * Create a new creator command instance.
     *
     * @param \Illuminate\Filesystem\Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return void
     *
     * @throws FileNotFoundException
     */
    public function handle()
    {
        if($this->option('readonly')){
            $this->readOnly = true;
        }

        $this->prepareVariablesForStubs($this->argument('modelName'));

        $this->createRepositoryInterface();

        $this->createRepository();

        $this->createController();

        $this->createTransformer();

        $this->addRoutes();

        $this->addProvider();

        $this->createTest();
    }

    /**
     * Prepare names, paths and namespaces for stubs.
     *
     * @param $name
     */
    protected function prepareVariablesForStubs($name)
    {
        $this->stubVariables['app']['namespace'] = $this->getAppNamespace();

        $baseDir = config('laravel-api-maker.models_base_dir');

        $this->modelsBaseNamespace = $baseDir ? trim($baseDir, '\\').'\\' : '';

        $this->setModelData($name)
            ->setRepositoryInterfaceData()
            ->setRepositoryData()
            ->setControllerData()
            ->setRouteData()
            ->setTransformerData()
            ->setTestData();
    }

    /**
     * Set the model name and namespace.
     *
     * @return $this
     */
    protected function setModelData($name)
    {
        if (str_contains($name, '/')) {
            $name = $this->convertSlashes($name);
        }

        $name = trim($name, '\\');

        $this->stubVariables['model']['fullNameWithoutRoot'] = $name;
        $this->stubVariables['model']['fullName'] = $this->stubVariables['app']['namespace'].$this->modelsBaseNamespace.$name;

        $exploded = explode('\\', $this->stubVariables['model']['fullName']);
        $this->stubVariables['model']['name'] = array_pop($exploded);
        $this->stubVariables['model']['namespace'] = implode('\\', $exploded);

        $exploded = explode('\\', $this->stubVariables['model']['fullNameWithoutRoot']);
        array_pop($exploded);
        $this->stubVariables['model']['additionalNamespace'] = implode('\\', $exploded);

        return $this;
    }

    /**
     * Set the repository interface names and namespaces.
     *
     * @return $this
     */
    protected function setRepositoryInterfaceData()
    {
        return $this->setDataForEntity('repositoryInterface');
    }

    /**
     * Set the repository class names and namespaces.
     *
     * @return $this
     */
    protected function setRepositoryData()
    {
        return $this->setDataForEntity('repository');
    }

    /**
     * Set the controller names and namespaces.
     *
     * @return $this
     */
    protected function setControllerData()
    {
        return $this->setDataForEntity('controller');
    }

    /**
     * Set route data for a given model.
     * "Profile\Payer" -> "profile_payers".
     *
     * @return $this
     */
    protected function setRouteData()
    {
        $name = str_replace('\\', '', $this->stubVariables['model']['fullNameWithoutRoot']);
        $name = snake_case($name);

        $this->stubVariables['route']['name'] = str_plural($name);

        return $this;
    }

    /**
     * Set the transformer names and namespaces.
     *
     * @return $this
     */
    protected function setTransformerData()
    {
        return $this->setDataForEntity('transformer');
    }

    /**
     * Set the bdd-style unit test class names and namespaces.
     *
     * @return $this
     */
    protected function setTestData()
    {
        return $this->setDataForEntity('test');
    }

    /**
     *  Set entity's names and namespaces.
     *
     * @param string $entity
     *
     * @return $this
     */
    protected function setDataForEntity($entity)
    {
        $entityNamespace = $this->convertSlashes(config("laravel-api-maker.{$entity}_dir"));
        $this->stubVariables[$entity]['name'] = $this->stubVariables['model']['name'].ucfirst($entity);

        $this->stubVariables[$entity]['namespaceWithoutRoot'] = implode('\\', array_filter([
            $entityNamespace,
            $this->stubVariables['model']['additionalNamespace'],
        ]));

        $this->stubVariables[$entity]['namespaceBase'] = $this->stubVariables['app']['namespace'].$entityNamespace;

        $this->stubVariables[$entity]['namespace'] = $this->stubVariables['app']['namespace'].$this->stubVariables[$entity]['namespaceWithoutRoot'];

        $this->stubVariables[$entity]['fullNameWithoutRoot'] = $this->stubVariables[$entity]['namespaceWithoutRoot'].'\\'.$this->stubVariables[$entity]['name'];

        $this->stubVariables[$entity]['fullName'] = $this->stubVariables[$entity]['namespace'].'\\'.$this->stubVariables[$entity]['name'];

        return $this;
    }

    /**
     *  Create repository interface file from a stub.
     *
     * @throws FileNotFoundException
     */
    protected function createRepositoryInterface()
    {
        $this->createClass('repositoryInterface');
    }

    /**
     *  Create repository class file from a stub.
     *
     * @throws FileNotFoundException
     */
    protected function createRepository()
    {
        $this->createClass('repository');
    }

    /**
     *  Create controller class file from a stub.
     *
     * @throws FileNotFoundException
     */
    protected function createController()
    {
        $this->createClass('controller');
    }

    /**
     *  Create controller class file from a stub.
     *
     * @throws FileNotFoundException
     */
    protected function createTransformer()
    {
        $this->createClass('transformer');
    }

    /**
     *  Create bdd-style unit test file from a stub.
     *
     * @throws FileNotFoundException
     */
    protected function createTest()
    {
        $this->createTestClass('test');
    }

    /**
     *  Add routes to routes file.
     *
     * @throws FileNotFoundException
     */
    protected function addRoutes()
    {
        $stubCount = $this->constructStub(base_path(config('laravel-api-maker.count_stub')));
        $stubAssociate = $this->constructStub(base_path(config('laravel-api-maker.associate_stub')));
        $stubRoute = $this->constructStub(base_path(config('laravel-api-maker.route_stub')));

        $routesFile = base_path(config('laravel-api-maker.routes_file'));

        // read file
        $lines = file($routesFile);
        $lastLine = trim($lines[count($lines) - 1]);

        // modify file
        if (strcmp($lastLine, '});') === 0) {
            $lines[count($lines) - 1] = '    '."$stubCount\r\n";
            $lines[] = '    '."$stubAssociate\r\n";
            $lines[] = '    '.$stubRoute;
            $lines[] = "\r\n});\r\n";
        } else {
            $lines[] = "$stubCount\r\n";
            $lines[] = "$stubAssociate\r\n";
            $lines[] = "$stubRoute\r\n";
        }

        // save file
        $fp = fopen($routesFile, 'w');
        fwrite($fp, implode('', $lines));
        fclose($fp);

        $this->info('Routes added successfully.');
    }

    /**
     *  Add provider to Api service provider file.
     *
     * @throws FileNotFoundException
     */
    protected function addProvider()
    {
        $stub = $this->constructStub(base_path(config('laravel-api-maker.provider_stub')));

        $providersFile = app_path(config('laravel-api-maker.provider_file'));

        // read file
        $lines = file($providersFile);
        $penultimate = trim($lines[count($lines) - 2]);
        $lastLine = trim($lines[count($lines) - 1]);

        // modify file
        if (strcmp($penultimate, '}') === 0 && strcmp($lastLine, '}') === 0) {
            $lines[count($lines) - 2] = '    '.'    '.$stub;
            $lines[count($lines) - 1] = "\r\n    }\r\n}";
        }

        // save file
        $fp = fopen($providersFile, 'w');
        fwrite($fp, implode('', $lines));
        fclose($fp);

        $this->info('Provider added successfully.');
    }

    /**
     * Create class with a given type.
     *
     * @param $type
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function createClass($type)
    {
        $path = $this->getPath($this->stubVariables[$type]['fullNameWithoutRoot']);
        if ($this->files->exists($path)) {
            $this->error(ucfirst($type).' already exists!');

            return;
        }

        $this->makeDirectoryIfNeeded($path);

        $complement = '';
        if ($this->readOnly && $type == 'controller') {
            $complement = '_readonly';
        }

        $this->files->put($path, $this->constructStub(base_path(config('laravel-api-maker.'.$type.$complement.'_stub'))));

        $this->info(ucfirst($type).' created successfully.');
    }

    /**
     * Create a test class on the base path.
     *
     * @param $type
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function createTestClass($type)
    {
        $name = base_path($this->stubVariables[$type]['fullNameWithoutRoot']);

        $path = str_replace('\\', '/', $name).'.php';

        if ($this->files->exists($path)) {
            $this->error(ucfirst($type).' already exists!');

            return;
        }

        $this->makeDirectoryIfNeeded($path);

        $complement = '';
        if ($this->readOnly) {
            $complement = '_readonly';
        }

        $this->files->put($path, $this->constructStub(base_path(config('laravel-api-maker.'.$type.$complement.'_stub'))));

        $this->info(ucfirst($type).' created successfully.');
    }

    /**
     * Get the destination file path.
     *
     * @param string $name
     *
     * @return string
     */
    protected function getPath($name)
    {
        $name = str_replace($this->stubVariables['app']['namespace'], '', $name);

        return $this->laravel['path'].'/'.str_replace('\\', '/', $name).'.php';
    }

    /**
     * Build the directory for the class if needed.
     *
     * @param string $path
     */
    protected function makeDirectoryIfNeeded($path)
    {
        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }
    }

    /**
     * Get stub content and replace all stub placeholders
     * with data from $this->stubData.
     *
     * @param string $path
     *
     * @return string
     *
     * @throws FileNotFoundException
     */
    protected function constructStub($path)
    {
        $stub = $this->files->get($path);

        foreach ($this->stubVariables as $entity => $fields) {
            foreach ($fields as $field => $value) {
                $stub = str_replace("{{{$entity}.{$field}}}", $value, $stub);
            }
        }

        return $stub;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the model'],
        ];
    }

    /**
     * Convert "/" to "\".
     *
     * @param $string
     *
     * @return string
     */
    protected function convertSlashes($string)
    {
        return str_replace('/', '\\', $string);
    }
}
