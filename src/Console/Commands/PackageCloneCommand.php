<?php

namespace Luchavez\BoilerplateGenerator\Console\Commands;

use Illuminate\Console\Command;
use Luchavez\BoilerplateGenerator\Exceptions\MissingNameArgumentException;
use Luchavez\BoilerplateGenerator\Exceptions\PackageNotFoundException;
use Luchavez\BoilerplateGenerator\Traits\UsesCommandVendorPackageDomainTrait;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class PackageCloneCommand
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 *
 * @since  2021-12-06
 */
class PackageCloneCommand extends Command
{
    use UsesCommandVendorPackageDomainTrait;

    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'bg:package:clone';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clone a Laravel package using Git.';

    /**
     * Create a new console command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->addPackageArguments();

        $this->getDefinition()->addArgument(
            new InputArgument(
                'url',
                InputArgument::REQUIRED,
                'The Git URL of package to clone.'
            )
        );
    }

    /**
     * Execute the console command.
     *
     * @throws PackageNotFoundException|MissingNameArgumentException
     */
    public function handle(): void
    {
        $this->setVendorPackageDomain(false, false);

        if ($this->vendor_name && $this->package_name) {
            $this->call(
                'packager:git',
                [
                    'vendor' => $this->vendor_name,
                    'name' => $this->package_name,
                    'url' => $this->argument('url'),
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
