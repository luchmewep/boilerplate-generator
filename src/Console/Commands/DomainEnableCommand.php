<?php

namespace Luchavez\BoilerplateGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Composer;
use Illuminate\Support\Str;
use Luchavez\BoilerplateGenerator\Exceptions\MissingNameArgumentException;
use Luchavez\BoilerplateGenerator\Exceptions\PackageNotFoundException;
use Luchavez\BoilerplateGenerator\Traits\UsesCommandVendorPackageDomainTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class DomainEnableCommand
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 */
class DomainEnableCommand extends Command
{
    use UsesCommandVendorPackageDomainTrait;

    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'bg:domain:enable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enable a domain in Laravel or in a specific package.';

    /**
     * Create a new console command instance.
     *
     * @return void
     */
    public function __construct(protected Composer $composer)
    {
        parent::__construct();

        $this->addPackageDomainOptions(has_domain_choices: false, has_force_domain: false);
    }

    /**
     * Execute the console command.
     *
     * @throws PackageNotFoundException|MissingNameArgumentException
     */
    public function handle(): bool
    {
        $this->setVendorPackageDomain(show_domain_choices: false);

        $this->domain_name = $this->getNameInput();

        $domain = boilerplateGenerator()
            ->getSummarizedDomains(package: $this->package_dir, with_providers: true)
            ->get($this->domain_name);

        // Fail if not found or already enabled
        if (! $domain) {
            $this->failed('Domain not found: '.$this->domain_name);

            return self::FAILURE;
        } elseif ($domain['is_enabled']) {
            $this->failed('Domain is already enabled: '.$this->domain_name);

            return self::FAILURE;
        }

        $psr4_contents = [];
        $providers_contents = collect();
        $is_package_local = $this->package_dir && boilerplateGenerator()->isPackageLocal($this->package_dir);

        $path = $this->getComposerJsonPath($is_package_local);

        $add_to_psr4_contents = function ($domain) use (&$psr4_contents, $path) {
            $psr4_contents = array_merge($psr4_contents, $this->createPsr4ContentsForComposerJson($path, $domain));
        };

        $add_to_provider_contents = function (Collection $collection) use (&$providers_contents) {
            $providers_contents = $providers_contents->merge($collection);
        };

        $disabled_parents = boilerplateGenerator()
            ->getParentDomains($this->domain_name, $this->package_dir, with_providers: true)
            ->where('is_enabled', false);

        if ($disabled_parents->count()) {
            $parent_list = $disabled_parents->keys()->map(fn ($key) => $this->getBoldText($key))->implode(', ', ', and ');
            $this->warning('One or more parent domains are not enabled yet: '.$parent_list);
            if ($this->confirm('Enable parent domains?', true)) {
                $disabled_parents->each(function ($value, $key) use ($add_to_psr4_contents, $add_to_provider_contents) {
                    // Add the parent domain to PSR-4 contents
                    $add_to_psr4_contents($key);
                    $add_to_provider_contents($value['providers']);
                });
            } else {
                $this->failed('Failed to enable domain as one or more parent domains are not yet enabled.');

                return self::FAILURE;
            }
        }

        // Add the actual domain to PSR-4 contents and providers
        $add_to_psr4_contents($this->domain_name);
        $add_to_provider_contents($domain['providers']);

        $disabled_children = boilerplateGenerator()
            ->getSubDomains($this->domain_name, $this->package_dir, with_providers: true)
            ->where('is_enabled', false);

        if ($disabled_children->count()) {
            $child_list = $disabled_children->keys()->map(fn ($key) => $this->getBoldText($key))->implode(', ', ', and ');
            $this->warning('Found one or more child domains that are not enabled yet: '.$child_list);
            if ($this->confirm('Enable child domains?', $this->includeChildDomains())) {
                $disabled_children->each(function ($value, $key) use ($add_to_provider_contents, $add_to_psr4_contents) {
                    // Add the child domain to PSR-4 contents
                    $add_to_psr4_contents($key);
                    $add_to_provider_contents($value['providers']);
                });
            }
        }

        $this->ongoing('Adding PSR-4 contents to composer.json');

        // Add PSR-4 contents to composer.json
        $success_psr4 = add_contents_to_composer_json('autoload.psr-4', $psr4_contents, $path);

        // Add providers to composer.json or app.php
        $providers_contents->each(function ($value) use ($is_package_local, $path) {
            if ($this->package_dir && $is_package_local) {
                if (add_provider_to_composer_json($value, $path)) {
                    $this->done('Added provider to composer.json: '.$value);
                } else {
                    $this->warning('Failed to add provider to composer.json: '.$value);
                }
            } else {
                if (add_provider_to_app_config($value)) {
                    $this->done('Added provider to app.php config: '.$value);
                } else {
                    $this->warning('Failed to add provider to app.php config: '.$value);
                }
            }
        });

        if ($success_psr4) {
            $this->done('Successfully added PSR-4 to composer.json');
            $this->composerRequireOrDump();
        } else {
            $this->failed('Failed to add PSR-4 to composer.json');
        }

        return $success_psr4 ? self::SUCCESS : self::FAILURE;
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
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['with-children', null, InputOption::VALUE_NONE, 'Include child domains to enable.'],
        ];
    }

    /**
     * @return bool
     */
    public function includeChildDomains(): bool
    {
        return $this->option('with-children');
    }

    /**
     * Get the validated desired class name from the input.
     *
     * @return string
     */
    protected function getValidatedNameInput(): string
    {
        $name = trim($this->argument('name'));

        return trim(preg_replace('/[^a-z\d]+/i', '.', $name), '.');
    }

    /**
     * @param  bool  $is_package_local
     * @return string
     */
    protected function getComposerJsonPath(bool $is_package_local = true): string
    {
        $path = $is_package_local ? package_domain_path($this->package_dir) : null;

        return qualify_composer_json($path);
    }

    /**
     * @param  string|null  $path
     * @param  string  $domain
     * @return array
     */
    protected function createPsr4ContentsForComposerJson(?string $path, string $domain): array
    {
        $path_only = Str::before($path, 'composer.json');

        $contents = [
            package_domain_app_namespace($this->package_dir, $domain, true) => package_domain_app_path($this->package_dir, $domain, true),
            package_domain_factories_namespace($this->package_dir, $domain, true) => package_domain_factories_path($this->package_dir, $domain, true),
            package_domain_seeders_namespace($this->package_dir, $domain, true) => package_domain_seeders_path($this->package_dir, $domain, true),
        ];

        foreach ($contents as $namespace => $absolute_path) {
            $contents[$namespace] = Str::of($absolute_path)->after($path_only)->finish('/')->jsonSerialize();
        }

        return $contents;
    }

    /**
     * Packages need to be installed again if there are changes related to its service providers.
     *
     * @return bool
     */
    protected function composerRequireOrDump(): bool
    {
        if ($this->package_dir) {
            // Uninstall first
            $process = make_process(['composer', 'remove', $this->package_dir]);

            $this->ongoing('Uninstalling '.$this->package_dir);

            $process->start();

            $process->wait();

            if ($process->isSuccessful()) {
                $process = make_process(['composer', 'require', $this->package_dir]);

                $this->ongoing('Reinstalling '.$this->package_dir.' to reflect changes');

                $process->start();

                $process->wait();

                if ($process->isSuccessful()) {
                    $this->done('Reinstalled '.$this->package_dir);

                    return true;
                }
            }

            $this->failed('Failed to reinstall '.$this->package_dir);

            return false;
        }

        $this->ongoing('Executing composer dump');

        if ($this->composer->dumpAutoloads() == self::SUCCESS) {
            $this->done('Successfully executed composer dump');

            return true;
        }

        $this->failed('Composer dump encountered an error');

        return false;
    }
}
