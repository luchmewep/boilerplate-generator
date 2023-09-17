<?php

namespace Luchavez\BoilerplateGenerator\Traits;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

/**
 * Trait UsesCommandServiceTrait
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 */
trait UsesCommandServiceTrait
{
    use UsesCommandVendorPackageDomainTrait;

    /**
     * @var bool
     */
    protected bool $service_exists = false;

    /**
     * @param  string|null  $description
     * @return void
     */
    protected function addServiceOptions(string $description = null): void
    {
        $this->getDefinition()->addOption(
            new InputOption(
                'service',
                's',
                InputOption::VALUE_REQUIRED,
                $description ?? 'Service Container class.'
            )
        );
    }

    /**
     * @param  string  $service
     * @param  string|null  $additionalNamespace
     * @param  bool  $qualifyService
     * @param  bool  $disablePackageNamespaceTemporarily
     * @return bool
     */
    protected function checkServiceExists(
        string &$service,
        string $additionalNamespace = null,
        bool $qualifyService = true,
        bool $disablePackageNamespaceTemporarily = false
    ): bool {
        $service_copy = $service;

        if ($disablePackageNamespaceTemporarily) {
            $this->is_package_namespace_disabled = true;
        }

        if ($qualifyService) {
            $service = $this->qualifyService($service, $additionalNamespace);
        } else {
            $service = (string) $this->cleanClassNamespace($service);
        }

        $this->is_package_namespace_disabled = false;

        if (! ($this->service_exists = class_exists($service))) {
            $service = $service_copy;
        }

        return $this->service_exists;
    }

    /**
     * @return string|null
     */
    protected function getServiceFromOptions(): ?string
    {
        return $this->option('service');
    }

    /**
     * @return void
     */
    protected function addServiceReplaceNamespace(): void
    {
        if (($service = $this->getServiceFromOptions()) && (
            $this->checkServiceExists($service, 'Services') ||
            $this->checkServiceExists($service, 'Services', false) ||
            $this->checkServiceExists($service, 'Services', true, true)
        )
        ) {
            $this->addMoreCasedReplaceNamespace($service, 'Service');
        }
    }

    /**
     * Qualify the given model class base name.
     *
     * @param  string  $service
     * @param  string|null  $additionalNamespace
     * @return string
     */
    protected function qualifyService(string $service, string $additionalNamespace = null): string
    {
        $service = (string) $this->cleanClassNamespace($service);

        $rootServiceNamespace = trim($this->rootNamespace().$additionalNamespace, '\\');

        if (Str::startsWith($service, $rootServiceNamespace)) {
            return $service;
        }

        return $this->qualifyService($rootServiceNamespace.'\\'.$service, $additionalNamespace);
    }
}
