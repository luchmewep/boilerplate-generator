<?php

namespace Luchavez\BoilerplateGenerator\Console\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Console\Factories\FactoryMakeCommand;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Luchavez\BoilerplateGenerator\Exceptions\MissingNameArgumentException;
use Luchavez\BoilerplateGenerator\Exceptions\PackageNotFoundException;
use Luchavez\BoilerplateGenerator\Traits\UsesCommandEloquentModelTrait;

/**
 * Class ExtendedMakeFactory
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 *
 * @since  2021-11-15
 */
class ExtendedMakeFactory extends FactoryMakeCommand
{
    use UsesCommandEloquentModelTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'bg:make:factory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new model factory in Laravel or in a specific package.';

    /**
     * @param  Filesystem  $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct($files);

        $this->addPackageDomainOptions();

        $this->addModelOptions();
    }

    /*****  OVERRIDDEN FUNCTIONS *****/

    /**
     * @return bool|null
     *
     * @throws FileNotFoundException|PackageNotFoundException|MissingNameArgumentException
     */
    public function handle(): ?bool
    {
        $this->setVendorPackageDomain();

        $this->setModelFields(true);

        if ($success = ! parent::handle()) {
            $this->createFactoryTrait();
        }

        return $success;
    }

    /**
     * return void
     *
     * @throws MissingNameArgumentException
     */
    protected function createFactoryTrait(): void
    {
        if (($this->package_name || $this->domain_name) && $this->model_name) {
            $this->call(
                'bg:make:trait',
                array_merge(
                    $this->getPackageArgs(),
                    [
                        'name' => 'Has'.$this->getNameInput(),
                        '--factory' => $this->qualifyClass($this->getNameInput()),
                    ]
                )
            );
        }
    }

    /**
     * @return string
     */
    protected function getStub(): string
    {
        return __DIR__.'/../../../stubs/factory/factory.custom.stub';
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name): string
    {
        $name = Str::of($name)
            ->replaceFirst(package_domain_factories_namespace($this->package_dir, $this->domain_dir), '')
            ->replace('\\', '/')
            ->finish('Factory')
            ->jsonSerialize();

        $path = $this->getPackageDomainFullPath();

        return $path.'/'.$name.'.php';
    }

    /**
     * @param $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return rtrim(package_domain_factories_namespace($this->package_dir, $this->domain_dir), '\\');
    }

    /**
     * Class type to append on filename.
     *
     * @return string|null
     */
    protected function getClassType(): ?string
    {
        return 'Factory';
    }

    /**
     * @return string
     */
    protected function getPackageDomainFullPath(): string
    {
        return package_domain_factories_path($this->package_dir, $this->domain_dir);
    }

    /**
     * Parse the class name and format according to the root namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function qualifyClass($name): string
    {
        $name = ltrim($name, '\\/');

        $name = str_replace('/', '\\', $name);

        $rootNamespace = package_domain_factories_namespace($this->package_dir, $this->domain_dir);

        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }

        return $this->qualifyClass(
            $this->getDefaultNamespace(trim($rootNamespace, '\\')).'\\'.$name
        );
    }
}
