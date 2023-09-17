<?php

namespace Luchavez\BoilerplateGenerator\Console\Commands;

use Illuminate\Support\Collection;
use function Laravel\Prompts\confirm;
use Luchavez\BoilerplateGenerator\Exceptions\MissingNameArgumentException;
use Luchavez\BoilerplateGenerator\Exceptions\PackageNotFoundException;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class DomainDisableCommand
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 */
class DomainDisableCommand extends DomainEnableCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'bg:domain:disable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Disable a domain or module in Laravel or in a specific package.';

    /**
     * Execute the console command.
     *
     * @return bool
     *
     * @throws MissingNameArgumentException
     * @throws PackageNotFoundException
     */
    public function handle(): bool
    {
        $this->setVendorPackageDomain(show_domain_choices: false);

        $this->domain_name = $this->getNameInput();

        $domain = boilerplateGenerator()
            ->getSummarizedDomains(package: $this->package_dir, with_providers: true)
            ->get($this->domain_name);

        // Fail if not found or already disabled
        if (! $domain) {
            $this->failed('Domain not found: '.$this->domain_name);

            return self::FAILURE;
        } elseif (! $domain['is_enabled'] && ! $domain['is_loaded']) {
            $this->failed('Domain is already disabled: '.$this->domain_name);

            return self::FAILURE;
        }

        $psr4_contents = [];
        $providers_contents = collect();
        $is_enabled_inside_package = $domain['is_enabled'];

        $path = $this->getComposerJsonPath($is_enabled_inside_package);

        $add_to_psr4_contents = function ($domain) use (&$psr4_contents, $path) {
            $psr4_contents = array_merge($psr4_contents, $this->createPsr4ContentsForComposerJson($path, $domain));
        };

        $add_to_provider_contents = function (Collection $collection) use (&$providers_contents) {
            $providers_contents = $providers_contents->merge($collection);
        };

        $enabled_children = boilerplateGenerator()
            ->getSubDomains($this->domain_name, $this->package_dir, with_providers: true)
            ->where('is_enabled', true);

        if ($enabled_children->count()) {
            $child_list = $enabled_children->keys()->map(fn ($key) => $this->getBoldText($key))->implode(', ', ', and ');
            $this->warning('One or more child domains are still enabled: '.$child_list);
            if (confirm('Disable child domains?', required: true)) {
                $enabled_children->each(function ($value, $key) use ($add_to_psr4_contents, $add_to_provider_contents) {
                    // Add the parent domain to PSR-4 contents
                    $add_to_psr4_contents($key);
                    $add_to_provider_contents($value['providers']);
                });
            } else {
                $this->failed('Failed to disable domain as one or more child domains are not still enabled.');

                return self::FAILURE;
            }
        }

        // Add the actual domain to PSR-4 contents
        $add_to_psr4_contents($this->domain_name);
        $add_to_provider_contents($domain['providers']);

        if ($this->includeParentDomains()) {
            $enabled_parents = boilerplateGenerator()
                ->getParentDomains($this->domain_name, $this->package_dir, with_providers: true)
                ->where('is_enabled', true);

            if ($enabled_parents->count()) {
                $enabled_parents->each(function ($value, $key) use ($add_to_provider_contents, $add_to_psr4_contents) {
                    // Add the child domain to PSR-4 contents
                    $add_to_psr4_contents($key);
                    $add_to_provider_contents($value['providers']);
                });
            }
        }

        $this->ongoing('Removing PSR-4 contents from composer.json');

        // Remove PSR-4 contents to composer.json
        $success_psr4 = remove_contents_from_composer_json('autoload.psr-4', $psr4_contents, $path);

        // Remove providers to composer.json or app.php
        $providers_contents->each(function ($value) use ($is_enabled_inside_package, $path) {
            if ($this->package_dir && $is_enabled_inside_package) {
                if (remove_provider_from_composer_json($value, $path)) {
                    $this->done('Removed provider from composer.json: '.$value);
                } else {
                    $this->warning('Failed to remove provider from composer.json: '.$value);
                }
            } else {
                if (remove_provider_from_app_config($value)) {
                    $this->done('Removed provider from app.php config: '.$value);
                } else {
                    $this->warning('Failed to remove provider from app.php config: '.$value);
                }
            }
        });

        if ($success_psr4) {
            $this->done('Successfully removed PSR-4 from composer.json');
            $this->composerRequireOrDump();
        } else {
            $this->failed('Failed to remove PSR-4 to composer.json');
        }

        return $success_psr4 ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['with-parents', 'p', InputOption::VALUE_NONE, 'Include parent domains.'],
        ];
    }

    /**
     * @return bool
     */
    public function includeParentDomains(): bool
    {
        return $this->option('with-parents');
    }
}
