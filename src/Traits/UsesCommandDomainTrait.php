<?php

namespace Luchavez\BoilerplateGenerator\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use Luchavez\BoilerplateGenerator\Console\Commands\RouteMakeCommand;
use Luchavez\StarterKit\Traits\UsesCommandCustomMessagesTrait;
use Symfony\Component\Console\Input\InputOption;

/**
 * Trait UsesCommandDomainTrait
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 */
trait UsesCommandDomainTrait
{
    use UsesCommandCustomMessagesTrait;

    /**
     * @var string|null
     */
    protected ?string $domain_name = null;

    /**
     * @var string|null
     */
    protected ?string $domain_dir = null;

    /**
     * @var string|null
     */
    protected ?string $domain_namespace = null;

    /**
     * @var string
     */
    protected string $default_domain = 'none';

    /**
     * @param  bool  $has_force_domain
     * @return void
     */
    public function addDomainOptions(bool $has_force_domain = true): void
    {
        $this->getDefinition()->addOption(
            new InputOption(
                'domain',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Domain or module name.'
            )
        );

        if ($has_force_domain) {
            $this->getDefinition()->addOption(
                new InputOption(
                    'force-domain',
                    null,
                    InputOption::VALUE_NONE,
                    'Create domain if does not exist.'
                )
            );
        }
    }

    /**
     * @return bool
     */
    protected function shouldCreateDomain(): bool
    {
        return $this->hasOption('force-domain') && $this->option('force-domain');
    }

    /**
     * @return array
     */
    public function getDomainArgs(): array
    {
        $args['--domain'] = $this->domain_name ?? $this->default_domain;
        $args['--force-domain'] = $this->shouldCreateDomain();

        return $args;
    }

    /**
     * @param  string  $package_option_name
     * @param  string|null  $package_dir
     * @param  string|null  $package_namespace
     * @return void
     */
    public function setDomainFieldsFromOptions(
        string $package_option_name,
        string $package_dir = null,
        string $package_namespace = null
    ): void {
        if ($this->domain_name = $this->getDomainFromOptions($package_option_name, $package_dir)) {
            $this->domain_dir = ltrim(domain_decode($this->domain_name), '/');
            $this->domain_namespace = Str::of(domain_decode($this->domain_name, true))
                ->finish('\\')
                ->ltrim('\\')
                ->prepend($package_namespace)
                ->jsonSerialize();
        }
    }

    /**
     * @param  string  $package_option_name
     * @param  string|null  $package_dir
     * @return array|bool|string|null
     */
    public function getDomainFromOptions(string $package_option_name, string $package_dir = null): bool|array|string|null
    {
        $domain = $this->hasOption('domain') ? $this->option('domain') : null;

        if ($domain === $this->default_domain) {
            return null;
        }

        $domain = $domain ? $this->qualifyDomainName($domain) : null;
        $domain_choices = boilerplateGenerator()->getSummarizedDomains(package: $package_dir);

        // If domain is not null and domain choices is not empty...
        if ($domain && $domain_choices->count()) {
            if (($this instanceof RouteMakeCommand && $this->shouldCreateDomain()) || $domain_choices->has($domain)) {
                return $domain;
            }

            $this->failed('Domain not found: '.$domain);
            $choice = select(
                label: 'Choose what to do',
                options: [
                    'create new domain',
                    'choose from domains',
                ],
                default: 'create new domain'
            );

            if ($choice === 'create new domain') {
                return $this->createNewDomain($domain, $package_option_name, $domain_choices, $package_dir);
            }

            return $this->chooseFromDomains($domain_choices);
        }

        // If domain is null but domain choices is not empty...
        elseif ($domain_choices->count()) {
            return $this->chooseFromDomains($domain_choices);
        } elseif ($domain) {
            // If domain is not null but domain choices is empty...
            return $this->createNewDomain($domain, $package_option_name, $domain_choices, $package_dir);
        }

        return null;
    }

    /**
     * Qualify Domain Name
     *
     * @param  string|null  $name
     * @return string|null
     */
    protected function qualifyDomainName(?string $name): ?string
    {
        return trim(preg_replace('/[^a-z\d]+/i', '.', $name), '.');
    }

    /**
     * @param  string  $domain
     * @param  string  $package_option_name
     * @param  Collection|null  $domain_choices
     * @param  string|null  $package_dir
     * @return string
     */
    protected function createNewDomain(string $domain, string $package_option_name, Collection $domain_choices = null, string $package_dir = null): string
    {
        $domain = text(label: 'Enter new domain name', placeholder: $domain, default: $domain, required: true);

        if ($domain === $this->default_domain) {
            $domain = null;
        }

        $domain = $domain ? Str::studly($domain) : null;

        // create domain if it does not exist
        if ($domain) {
            if (! $this instanceof RouteMakeCommand && ! ($domain_choices?->contains($domain))) {
                $args = [
                    'name' => $domain,
                    '--no-interaction' => true,
                    '--'.$package_option_name => $package_dir,
                ];
                $this->call('bg:domain:create', $args);
            }

            return $domain;
        }

        $this->error('Failed to create new domain.');

        return $this->createNewDomain($domain, $package_option_name, $package_dir);
    }

    /**
     * @param  Collection|null  $domain_choices
     * @return string|null
     */
    public function chooseFromDomains(Collection $domain_choices = null): ?string
    {
        if ($domain_choices &&
            ($domain = select(
                label: 'Choose a domain',
                options: $domain_choices->keys()->prepend($this->default_domain)->toArray(),
                default: 0
            ))
        ) {
            if ($domain === $this->default_domain) {
                return null;
            }

            return $domain;
        }

        return null;
    }
}
