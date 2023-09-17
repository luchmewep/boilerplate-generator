<?php

namespace Luchavez\BoilerplateGenerator\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Filesystem\Filesystem;
use Luchavez\BoilerplateGenerator\Exceptions\MissingNameArgumentException;
use Luchavez\BoilerplateGenerator\Exceptions\PackageNotFoundException;
use Luchavez\BoilerplateGenerator\Traits\UsesCommandVendorPackageDomainTrait;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class DomainCreateCommand
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 */
class DomainCreateCommand extends GeneratorCommand
{
    use UsesCommandVendorPackageDomainTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'bg:domain:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a domain or module in Laravel or in a specific package.';

    /**
     * @var string
     */
    protected $type = 'Domain';

    /**
     * Create a new controller creator command instance.
     *
     * @param  Filesystem  $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct($files);

        $this->addPackageDomainOptions(has_domain_choices: false, has_force_domain: false);
    }

    /**
     * Execute the console command.
     *
     * @return bool|null
     *
     * @throws MissingNameArgumentException|PackageNotFoundException
     */
    public function handle(): ?bool
    {
        $this->setVendorPackageDomain(show_domain_choices: false);

        $this->domain_name = $this->getNameInput();

        $success = false;

        // Prepare Create Route Arguments & Options
        $route_args = $this->getPackageArgs();
        $route_args['--no-interaction'] = true;
        $route_args['--force-domain'] = true;

        // Prepare Enable Domain Arguments & Options
        $domain_args = $route_args;
        unset($domain_args['--force-domain'], $domain_args['--domain']);

        // Explode the domain name to create subdomains
        $domains = explode('.', $this->domain_name);

        // Initialize encoded domain
        $encoded_domain = null;

        for ($i = 0; $i < count($domains); $i++) {
            // Create parent domain first before subdomains
            $slice = array_slice($domains, 0, $i + 1);
            $encoded_domain = implode('.', $slice);

            if (! boilerplateGenerator()->isDomainLocal($encoded_domain, $this->package_dir)) {
                $route_args['--domain'] = $encoded_domain;

                // Prepare Create Provider Arguments & Options
                $provider_args = $route_args;

                // Create routes
                collect(['web', 'api'])->each(
                    function ($value) use ($route_args, &$success) {
                        $route_args['name'] = $value;
                        $route_args['--api'] = $value !== 'web';
                        if ($this->call('bg:make:route', $route_args) == self::SUCCESS) {
                            $success = true;
                        }
                    }
                );

                // Create Provider
                $provider_args['name'] = implode('', $slice);
                $provider_args['--starter-kit'] = true;
                $provider_args['--skip'] = true;
                $this->call('bg:make:provider', $provider_args);
            }
        }

        if ($success) {
            $this->done('Domain created successfully.');

            // Enable Domain
            if ($encoded_domain) {
                $domain_args['name'] = $encoded_domain;
                $this->call('bg:domain:enable', $domain_args);
            }
        } else {
            $this->failed('Domain was not created or already existing.');
        }

        return $success ? self::SUCCESS : self::FAILURE;
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
     * Get the stub file for the generator.
     *
     * @return string|null
     */
    protected function getStub(): ?string
    {
        return null;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'Domain or module name'],
        ];
    }

    /**
     * Get the validated desired class name from the input.
     * Filtered out domain string from name.
     *
     * @return string
     */
    protected function getValidatedNameInput(): string
    {
        $name = trim($this->argument('name'));

        $filtered = preg_replace('/[^a-z\d](domain(s)*)*+/i', '.', $name);

        return collect(explode('.', $filtered))->filter()->implode('.');
    }
}
