<?php

namespace Luchavez\BoilerplateGenerator\Console\Commands;

use Illuminate\Console\Command;
use Luchavez\BoilerplateGenerator\Exceptions\MissingNameArgumentException;
use Luchavez\BoilerplateGenerator\Exceptions\PackageNotFoundException;
use Luchavez\BoilerplateGenerator\Traits\UsesCommandVendorPackageDomainTrait;

/**
 * Class PackageDisableCommand
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 *
 * @since  2021-12-06
 */
class PackageDisableCommand extends Command
{
    use UsesCommandVendorPackageDomainTrait;

    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'bg:package:disable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Disable a Laravel package.';

    /**
     * Create a new console command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->addPackageArguments(false);
    }

    /**
     * Execute the console command.
     *
     * @throws PackageNotFoundException|MissingNameArgumentException
     */
    public function handle(): void
    {
        $this->setVendorPackageDomain(show_domain_choices: false, show_default_package: false, is_loaded: true);

        if ($this->vendor_name && $this->package_name) {
            $this->call(
                'packager:disable',
                [
                    'vendor' => $this->vendor_name,
                    'name' => $this->package_name,
                ]
            );
        }
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
