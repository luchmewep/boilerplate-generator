<?php

namespace Luchavez\BoilerplateGenerator\Traits;

use Illuminate\Support\Collection;
use Symfony\Component\Console\Input\InputOption;

/**
 * Trait UsesCommandMultipleTargetsTrait
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 */
trait UsesCommandMultipleTargetsTrait
{
    use UsesCommandVendorPackageDomainTrait;

    /**
     * @var array|null
     */
    protected ?array $targets = null;

    /**
     * @var string|null
     */
    protected ?string $domain_search = null;

    /**
     * @param  string  $option_name
     * @return void
     */
    public function addMultipleTargetsOption(string $option_name = 'package'): void
    {
        $this->package_option_argument_name = $option_name;

        $this->getDefinition()->addOptions([
            new InputOption('all', 'a', InputOption::VALUE_NONE, 'Apply to Laravel and packages.'),
            new InputOption('packages', 'p', InputOption::VALUE_NONE, 'Apply to packages only.'),
            new InputOption($this->package_option_argument_name, null, InputOption::VALUE_OPTIONAL, 'Apply to  (e.g., `vendor-name/package-name`).'),
            new InputOption('domain', null, InputOption::VALUE_REQUIRED, 'Apply to a domain and its subdomains.'),
        ]);
    }

    /**
     * @return void
     */
    protected function setTargetsAndDomains(): void
    {
        $this->targets = $this->getPackageFromOptions(true);

        $this->domain_search = $this->getDomainFromOption();

        // check if it has default package
        $has_root = $this->hasRoot();

        // show choices if neither --all or --packages is used
        if (! $this->isRootAndPackages() && ! $this->isPackagesOnly()) {
            $default_choices = $this->targets ?
                boilerplateGenerator()->getSummarizedPackages($this->targets)->keys() :
                collect();

            // add back 'root' to the list of default choices if previously typed
            $default_choices = $default_choices
                ->when($has_root, fn (Collection $collection) => $collection->prepend($this->default_package))
                ->toArray();

            $this->targets = $this->choosePackageFromList(
                is_local: null,
                is_loaded: true,
                multiple: true,
                default_choices: $default_choices
            );
        } else {
            $this->targets = null;
        }
    }

    /**
     * @return bool
     */
    protected function isRootAndPackages(): bool
    {
        return $this->option('all');
    }

    /**
     * @return bool
     */
    protected function isPackagesOnly(): bool
    {
        return $this->option('packages');
    }

    /**
     * @return bool
     */
    protected function hasRoot(): bool
    {
        return $this->targets && in_array($this->default_package, $this->targets);
    }

    /**
     * @return bool
     */
    protected function hasOtherThanRoot(): bool
    {
        return $this->hasRoot() ? count($this->targets) > 1 : ($this->targets && count($this->targets));
    }

    /**
     * @return string|null
     */
    protected function getDomainFromOption(): ?string
    {
        return $this->option('domain');
    }
}
