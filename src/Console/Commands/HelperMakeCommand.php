<?php

namespace Luchavez\BoilerplateGenerator\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Luchavez\BoilerplateGenerator\Exceptions\MissingNameArgumentException;
use Luchavez\BoilerplateGenerator\Exceptions\PackageNotFoundException;
use Luchavez\BoilerplateGenerator\Traits\UsesCommandServiceTrait;

/**
 * Class HelperMakeCommand
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 */
class HelperMakeCommand extends GeneratorCommand
{
    use UsesCommandServiceTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'bg:make:helper';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new helper file in Laravel or in a specific package.';

    /**
     * @var string
     */
    protected $type = 'Helper';

    /**
     * Create a new controller creator command instance.
     *
     * @param  Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct($files);

        $this->addPackageDomainOptions(true);

        $this->addServiceOptions();
    }

    /**
     * @return bool|null
     *
     * @throws MissingNameArgumentException|PackageNotFoundException|FileNotFoundException
     */
    public function handle(): ?bool
    {
        $this->setVendorPackageDomain();

        $this->addServiceReplaceNamespace();

        return parent::handle();
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub(): string
    {
        return $this->moreReplaceNamespace?->has('NamespacedService') ?
            __DIR__.'/../../../stubs/helper/helper.service.custom.stub' :
            __DIR__.'/../../../stubs/helper/helper.custom.stub';
    }

    /**
     * Class type to append on filename.
     *
     * @return string|null
     */
    protected function getClassType(): ?string
    {
        return 'Helper';
    }

    /**
     * Get the validated desired class name from the input.
     *
     * @return string
     */
    protected function getValidatedNameInput(): string
    {
        $classType = Str::snake($this->getClassType(), '-');
        $name = trim($this->argument('name'));

        if ($classType) {
            return Str::of($name)->snake('-')->before($classType)->trim('-')->append('-', $classType);
        }

        return $name;
    }

    /**
     * @return string
     */
    protected function getPackageDomainFullPath(): string
    {
        return package_domain_helpers_path($this->package_dir, $this->domain_dir);
    }
}
