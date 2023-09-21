<?php

namespace Luchavez\BoilerplateGenerator\Console\Commands;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Console\ModelMakeCommand;
use Illuminate\Support\Composer;
use Illuminate\Support\Str;
use Luchavez\BoilerplateGenerator\Exceptions\MissingNameArgumentException;
use Luchavez\BoilerplateGenerator\Exceptions\PackageNotFoundException;
use Luchavez\BoilerplateGenerator\Traits\UsesCommandVendorPackageDomainTrait;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ExtendedMakeModel
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 *
 * @since  2021-11-09
 */
class ExtendedMakeModel extends ModelMakeCommand
{
    use UsesCommandVendorPackageDomainTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'bg:make:model';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Eloquent model class in Laravel or in a specific package.';

    /**
     * Create a new controller creator command instance.
     *
     * @param  Filesystem  $files
     * @param  Composer  $composer
     */
    public function __construct(Filesystem $files, protected Composer $composer)
    {
        parent::__construct($files);

        $this->addPackageDomainOptions();
    }

    /*****
     * OVERRIDDEN FUNCTIONS
     *****/

    /**
     * @return void
     *
     * @throws PackageNotFoundException|MissingNameArgumentException
     */
    public function handle(): void
    {
        $this->setVendorPackageDomain();

        if ($this->option('all')) {
            $this->input->setOption('observer', true);
            $this->input->setOption('repository', true);
            $this->input->setOption('df', true);
        }

        parent::handle();

        if ($this->option('observer')) {
            $this->createObserver();
        }

        if ($this->option('repository')) {
            $this->createRepository();
        }

        if ($this->option('df')) {
            $this->createDataFactory();
        }
    }

    /**
     * Create a migration file for the model.
     *
     * @return void
     */
    protected function createMigration(): void
    {
        $table = Str::snake(Str::pluralStudly(class_basename($this->argument('name'))));

        if ($this->option('pivot')) {
            $table = Str::singular($table);
        }

        $args = $this->getPackageArgs();
        $args['name'] = "create_{$table}_table";
        $args['--create'] = $table;
        $args['--no-interaction'] = true;

        $this->call('bg:make:migration', $args);
    }

    /**
     * Create a model factory for the model.
     *
     * @return void
     */
    protected function createFactory(): void
    {
        $name = $this->argument('name');

        $factory = Str::studly($name);

        $args = $this->getPackageArgs();
        $args['name'] = $factory;
        $args['--model'] = $this->qualifyClass($name);
        $args['--no-interaction'] = true;

        $this->call('bg:make:factory', $args);
    }

    /**
     * Create a seeder file for the model.
     *
     * @return void
     */
    protected function createSeeder(): void
    {
        $seeder = Str::studly(class_basename($this->argument('name')));

        $args = $this->getPackageArgs();
        $args['name'] = $seeder;
        $args['--no-interaction'] = true;

        $this->call('bg:make:seeder', $args);
    }

    /**
     * Create a controller for the model.
     *
     * @return void
     */
    protected function createController(): void
    {
        $name = $this->argument('name');

        $controller = Str::studly(class_basename($name));

        $modelName = $this->qualifyClass($name);

        $args = $this->getPackageArgs();
        $args['name'] = $controller;
        $args['--model'] = $this->option('resource') || $this->option('api') ? $modelName : null;
        $args['--api'] = $this->option('api');
        $args['--skip-model'] = true;
        $args['--no-interaction'] = true;

        $this->call('bg:make:controller', array_filter($args));
    }

    /**
     * Create a repository file for the model.
     *
     * @return void
     */
    protected function createRepository(): void
    {
        $name = $this->argument('name');

        $repository = Str::studly(class_basename($name));

        $args = $this->getPackageArgs();
        $args['name'] = $repository;
        $args['--no-interaction'] = true;

        $this->call('bg:make:repository', $args);
    }

    /**
     * Create an observer file for the model.
     *
     * @return void
     */
    protected function createObserver(): void
    {
        $repository = Str::studly(class_basename($this->argument('name')));

        $args = $this->getPackageArgs();
        $args['name'] = $repository;
        $args['--model'] = $this->qualifyClass($repository);
        $args['--no-interaction'] = true;

        $this->call('bg:make:observer', $args);
    }

    /**
     * Create a policy file for the model.
     *
     * @return void
     */
    protected function createPolicy(): void
    {
        $name = $this->argument('name');

        $policy = Str::studly(class_basename($name));

        $args = $this->getPackageArgs();
        $args['name'] = $policy;
        $args['--model'] = $this->qualifyClass($name);
        $args['--no-interaction'] = true;

        $this->call('bg:make:policy', $args);
    }

    /**
     * Create a policy file for the model.
     *
     * @return void
     */
    protected function createDataFactory(): void
    {
        $dataFactory = Str::studly(class_basename($this->argument('name')));

        $args = $this->getPackageArgs();
        $args['name'] = $dataFactory;
        $args['--model'] = $this->qualifyClass($dataFactory);
        $args['--no-interaction'] = true;

        $this->call('bg:make:df', $args);
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub(): string
    {
        return __DIR__.'/../../../stubs/model/model.custom.stub';
    }

    /**
     * @return array
     */
    protected function getOptions(): array
    {
        return array_merge(
            parent::getOptions(),
            [
                ['repository', null, InputOption::VALUE_NONE, 'Create new repository class based on the model.'],
                ['observer', 'o', InputOption::VALUE_NONE, 'Create new observer class based on the model.'],
                ['df', null, InputOption::VALUE_NONE, 'Create new data factory class based on the model.'],
            ]
        );
    }

    /**
     * Class type to append on filename.
     *
     * @return string|null
     */
    protected function getClassType(): ?string
    {
        return null;
    }
}
