<?php

namespace Luchavez\BoilerplateGenerator\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Luchavez\BoilerplateGenerator\Exceptions\MissingNameArgumentException;
use Luchavez\BoilerplateGenerator\Exceptions\PackageNotFoundException;
use Luchavez\BoilerplateGenerator\Traits\UsesCommandVendorPackageDomainTrait;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class TraitMakeCommand
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 *
 * @since  2021-11-10
 */
class TraitMakeCommand extends GeneratorCommand
{
    use UsesCommandVendorPackageDomainTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'bg:make:trait';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new interface in Laravel or in a specific package.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Trait';

    public function __construct(Filesystem $files)
    {
        parent::__construct($files);

        $this->addPackageDomainOptions();

        $this->addFactoryOptions();
    }

    /*****
     * OVERRIDDEN FUNCTIONS
     *****/

    /**
     * @return bool|null
     *
     * @throws FileNotFoundException
     * @throws PackageNotFoundException|MissingNameArgumentException
     */
    public function handle(): ?bool
    {
        $this->setVendorPackageDomain();

        $this->setFactoryFields();

        return parent::handle();
    }

    /**
     * @return void
     */
    protected function addFactoryOptions(): void
    {
        $this->getDefinition()->addOption(
            new InputOption(
                'factory',
                'f',
                InputOption::VALUE_REQUIRED,
                'Factory to be included.'
            )
        );
    }

    /**
     * @return void
     */
    protected function setFactoryFields(): void
    {
        if ($factory = $this->option('factory')) {
            $factory = $this->qualifyFactoryClass($factory);
            $this->setFactoryName($factory);
            $this->setFactoryClass($factory);
            $this->addMoreCasedReplaceNamespace($factory, 'Factory');
        }
    }

    /**
     * @return string
     */
    protected function getStub(): string
    {
        return __DIR__.'/../../../stubs/trait/trait'.($this->option('factory') ? '.factory' : '').'.custom.stub';
    }

    /**
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'/Traits';
    }

    /**
     * Class type to append on filename.
     *
     * @return string|null
     */
    protected function getClassType(): ?string
    {
        return 'Trait';
    }

    /*****
     * SETTERS & GETTERS
     *****/

    /**
     * @param  string|null  $factory
     */
    public function setFactoryClass(?string $factory): void
    {
        $this->addMoreReplaceNamespace(
            [
                'FactoryClass' => $factory,
            ]
        );
    }

    /**
     * @param  string|null  $factory
     */
    public function setFactoryName(?string $factory): void
    {
        $this->addMoreReplaceNamespace(
            [
                'FactoryName' => Str::of($factory)->afterLast('\\'),
            ]
        );
    }

    /**
     * Parse the class name and format according to the root namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function qualifyFactoryClass(string $name): string
    {
        $name = (string) $this->cleanClassNamespace($name);

        $rootNamespace = package_domain_factories_namespace($this->package_dir, $this->domain_dir);

        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }

        return $this->qualifyFactoryClass($rootNamespace.$name);
    }
}
