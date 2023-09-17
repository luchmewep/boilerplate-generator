<?php

namespace Luchavez\BoilerplateGenerator\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Luchavez\BoilerplateGenerator\Exceptions\MissingNameArgumentException;
use Luchavez\BoilerplateGenerator\Exceptions\PackageNotFoundException;
use Luchavez\BoilerplateGenerator\Traits\UsesCommandVendorPackageDomainTrait;

/**
 * Class ConfigMakeCommand
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 */
class ConfigMakeCommand extends GeneratorCommand
{
    use UsesCommandVendorPackageDomainTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'bg:make:config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new config file in Laravel or in a specific package.';

    /**
     * @var string
     */
    protected $type = 'Config';

    /**
     * Create a new config make command instance.
     *
     * @param  Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct($files);

        $this->addPackageDomainOptions(true);
    }

    /**
     * @return bool|null
     *
     * @throws MissingNameArgumentException|PackageNotFoundException|FileNotFoundException
     */
    public function handle(): ?bool
    {
        $this->setVendorPackageDomain();

        return parent::handle();
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub(): string
    {
        return __DIR__.'/../../../stubs/config/config.custom.stub';
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

    /**
     * Get the validated desired class name from the input.
     *
     * @return string
     */
    protected function getValidatedNameInput(): string
    {
        return Str::of($this->argument('name'))->snake('-')->jsonSerialize();
    }

    /**
     * @return string
     */
    protected function getPackageDomainFullPath(): string
    {
        return package_domain_config_path($this->package_dir, $this->domain_dir);
    }
}
