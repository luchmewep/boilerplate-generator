<?php

namespace Luchavez\BoilerplateGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Composer;
use Luchavez\BoilerplateGenerator\Exceptions\MissingNameArgumentException;
use Luchavez\BoilerplateGenerator\Exceptions\PackageNotFoundException;
use Luchavez\BoilerplateGenerator\Traits\UsesCommandVendorPackageDomainTrait;

/**
 * Class PackageRemoveCommand
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 *
 * @since  2021-12-06
 */
class PackageRemoveCommand extends Command
{
    use UsesCommandVendorPackageDomainTrait;

    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'bg:package:remove';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove a Laravel package.';

    /**
     * Create a new console command instance.
     *
     * @return void
     */
    public function __construct(protected Composer $composer)
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
        $this->setVendorPackageDomain(true, false, false);

        if ($this->vendor_name && $this->package_name) {
            $this->call(
                'packager:remove',
                [
                    'vendor' => $this->vendor_name,
                    'name' => $this->package_name,
                    '--no-interaction' => true,
                ]
            );
        }

        // Run composer dump
        $this->composer->dumpAutoloads();
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
