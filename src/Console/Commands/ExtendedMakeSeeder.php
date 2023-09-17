<?php

namespace Luchavez\BoilerplateGenerator\Console\Commands;

use Illuminate\Database\Console\Seeds\SeederMakeCommand;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Luchavez\BoilerplateGenerator\Exceptions\MissingNameArgumentException;
use Luchavez\BoilerplateGenerator\Exceptions\PackageNotFoundException;
use Luchavez\BoilerplateGenerator\Traits\UsesCommandVendorPackageDomainTrait;

/**
 * Class ExtendedMakeSeeder
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 *
 * @since  2021-11-15
 */
class ExtendedMakeSeeder extends SeederMakeCommand
{
    use UsesCommandVendorPackageDomainTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'bg:make:seeder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new seeder class in Laravel or in a specific package.';

    /**
     * Create a new controller creator command instance.
     *
     * @param  Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $files)
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

        parent::handle();
    }

    /**
     * @return string
     */
    protected function getStub(): string
    {
        return __DIR__.'/../../../stubs/seeder/seeder.custom.stub';
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
            ->replaceFirst(package_domain_seeders_namespace($this->package_dir, $this->domain_dir), '')
            ->replace('\\', '/')
            ->finish('Seeder')
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
        return rtrim(package_domain_seeders_namespace($this->package_dir, $this->domain_dir), '\\');
    }

    /**
     * Class type to append on filename.
     *
     * @return string|null
     */
    protected function getClassType(): ?string
    {
        return 'Seeder';
    }

    /**
     * @return string
     */
    protected function getPackageDomainFullPath(): string
    {
        return package_domain_seeders_path($this->package_dir, $this->domain_dir);
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

        $rootNamespace = package_domain_seeders_namespace($this->package_dir, $this->domain_dir);

        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }

        return $this->qualifyClass(
            $this->getDefaultNamespace(trim($rootNamespace, '\\')).'\\'.$name
        );
    }
}
