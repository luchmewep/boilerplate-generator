<?php

namespace Luchavez\BoilerplateGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Composer;
use Luchavez\BoilerplateGenerator\Exceptions\MissingNameArgumentException;
use Luchavez\BoilerplateGenerator\Exceptions\PackageNotFoundException;
use Luchavez\BoilerplateGenerator\Traits\UsesCommandVendorPackageDomainTrait;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class PackageCreateCommand
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 *
 * @since  2021-11-09
 */
class PackageCreateCommand extends Command
{
    use UsesCommandVendorPackageDomainTrait;

    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'bg:package:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Laravel package.';

    /**
     * Create a new console command instance.
     *
     * @return void
     */
    public function __construct(protected Composer $composer)
    {
        parent::__construct();

        $this->addPackageArguments();
    }

    /**
     * Execute the console command.
     *
     * @throws PackageNotFoundException|MissingNameArgumentException
     */
    public function handle(): void
    {
        $this->setVendorPackageDomain(false, false);

        // set the author details of Packager
        config(['packager.author_name' => boilerplateGenerator()->getAuthorName()]);
        config(['packager.author_email' => boilerplateGenerator()->getAuthorEmail()]);
        config(['packager.author_homepage' => boilerplateGenerator()->getAuthorHomepage()]);
        config(['packager.skeleton' => boilerplateGenerator()->getPackageSkeleton()]);

        $this->call(
            'packager:new',
            [
                'vendor' => $this->vendor_name,
                'name' => $this->package_name,
                '--i' => true,
            ]
        );

        // Run composer dump
        $this->composer->dumpAutoloads();

        // Run all necessary file generators
        collect(['web', 'api'])->each(
            function ($value) {
                $this->call(
                    'bg:make:route',
                    [
                        'name' => $value,
                        '--package' => $this->package_dir,
                        '--api' => $value !== 'web',
                        '--no-interaction' => true,
                    ]
                );
            }
        );

        $this->call(
            'bg:gitlab:publish',
            [
                '--package' => $this->package_dir,
                '--no-interaction' => true,
            ]
        );

        $this->call(
            'bg:pest:install',
            [
                '--package' => $this->package_dir,
                '--domain' => $this->domain_name,
                '--no-interaction' => true,
            ]
        );
    }

    /**
     * @return array
     */
    protected function getOptions(): array
    {
        return array_merge(
            [
                ['yes', 'y', InputOption::VALUE_NONE, 'Yes to all generate questions.'],
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
