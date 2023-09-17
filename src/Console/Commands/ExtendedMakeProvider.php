<?php

namespace Luchavez\BoilerplateGenerator\Console\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Console\ProviderMakeCommand;
use Luchavez\BoilerplateGenerator\Exceptions\MissingNameArgumentException;
use Luchavez\BoilerplateGenerator\Exceptions\PackageNotFoundException;
use Luchavez\BoilerplateGenerator\Traits\UsesCommandVendorPackageDomainTrait;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ExtendedMakeProvider
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 *
 * @since  2021-11-19
 */
class ExtendedMakeProvider extends ProviderMakeCommand
{
    use UsesCommandVendorPackageDomainTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'bg:make:provider';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new service provider class in Laravel or in a specific package.';

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

    /***** OVERRIDDEN FUNCTIONS *****/

    /**
     * @return bool|null
     *
     * @throws FileNotFoundException|PackageNotFoundException|MissingNameArgumentException
     */
    public function handle(): ?bool
    {
        $this->setVendorPackageDomain();

        $handled = parent::handle();

        // If success, enable the domain
        if (is_null($handled) && ! $this->skipEnable()) {
            $class = $this->qualifyClass($this->getNameInput());

            if ($this->package_dir) {
                add_provider_to_composer_json($class, package_domain_path($this->package_dir));
            } else {
                add_provider_to_app_config($class);
            }
        }

        return $handled ? self::SUCCESS : self::FAILURE;
    }

    /**
     * @return string
     */
    protected function getStub(): string
    {
        if ($this->option('starter-kit')) {
            return __DIR__.'/../../../stubs/provider/provider.sk.custom.stub';
        }

        return __DIR__.'/../../../stubs/provider/provider.custom.stub';
    }

    /**
     * Class type to append on filename.
     *
     * @return string|null
     */
    protected function getClassType(): ?string
    {
        return 'Provider';
    }

    /**
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['starter-kit', null, InputOption::VALUE_NONE, 'Extend the BaseStarterKitServiceProvider.'],
            ['skip', null, InputOption::VALUE_NONE, 'Skip enabling the provider.'],
        ];
    }

    /**
     * @return bool
     */
    public function skipEnable(): bool
    {
        return $this->option('skip');
    }
}
