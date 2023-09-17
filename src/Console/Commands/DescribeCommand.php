<?php

namespace Luchavez\BoilerplateGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Luchavez\BoilerplateGenerator\Traits\UsesCommandMultipleTargetsTrait;
use Luchavez\StarterKit\Abstracts\BaseStarterKitServiceProvider;
use Luchavez\StarterKit\Services\StarterKit;
use Symfony\Component\Console\Helper\TableSeparator;

/**
 * Class DescribeCommand
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 */
class DescribeCommand extends Command
{
    use UsesCommandMultipleTargetsTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'bg:describe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display all information about Laravel app and/or package/s.';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->addMultipleTargetsOption();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        // Set Symfony Console Formatter
        $this->setupOutputFormatters();

        $this->setTargetsAndDomains();

        $keys = ['path', 'directories', 'domains'];

        if ($this->hasRoot()) {
            $data = starterKit()->getRoot()->only($keys)->toArray();
            $data['package'] = null;

            $this->describePackage(...$data);
        }

        if ($this->hasOtherThanRoot()) {
            $this->newLine();
            boilerplateGenerator()
                ->getSummarizedPackages(is_loaded: true, with_details: true)
                ->only($this->targets)
                ->each(function ($arr, $package) use ($keys) {
                    $data = Arr::only($arr, $keys);
                    $data['package'] = $package;
                    $this->describePackage(...$data);
                });
        }

        return self::SUCCESS;
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
     * @param  string|null  $package
     * @param  string|null  $path
     * @param  array  $directories
     * @param  array  $domains
     * @return void
     */
    public function describePackage(string $package = null, string $path = null, array $directories = [], array $domains = []): void
    {
        if ($package) {
            $this->note("$package 📦", 'PACKAGE', false);
            $this->newLine();
        }

        // Describe Composer JSON metadata.
        $this->describeComposerJson($path);

        // Decide whether to display package directories or not.
        if ($this->domain_search && count($domains)) {
            $only = boilerplateGenerator()->getSubDomains(domain: $this->domain_search, package: $package, with_parent: true);
            $domains = collect($domains)->only($only->keys());
        } else {
            // Describe Laravel-related metadata.
            $this->displayDirectories($directories, $package);
        }

        // Describe domains-related metadata.
        collect($domains)->each(function ($item, $key) use ($package) {
            $this->newLine();
            $this->describeDomain($key, $package, $item['directories']);
        });
    }

    /**
     * @param  string  $domain
     * @param  string|null  $package
     * @param  array  $directories
     * @return void
     */
    public function describeDomain(string $domain, string $package = null, array $directories = []): void
    {
        $package = $package ? "$package 📦" : 'Laravel';
        $this->note($this->getBoldText($domain).' of '.$this->getBoldText($package), 'DOMAIN', false);
        $this->newLine();
        $this->displayDirectories($directories, $package);
    }

    public function describeComposerJson(string $path)
    {
        if ($contents = getContentsFromComposerJson($path)) {
            // Package Metadata
            $keywords = implode(', ', $contents->get('keywords', []));

            $this->line("Name\t\t: ".$this->getBoldText($contents->get('name')));
            $this->line("Description\t: ".$this->getBoldText($contents->get('description')));
            $this->line("Homepage\t: ".$this->getBoldText($contents->get('homepage')));
            $this->line("License\t\t: ".$this->getBoldText($contents->get('license')));
            $this->line("Keywords\t: ".$this->getBoldText($keywords));
            $this->newLine();

            // Display Dependencies
            $this->displayDependenciesTable($contents->get('require'), $contents->get('require-dev'));

            // Authors Metadata
            if ($authors = $contents->get('authors')) {
                $rows = collect($authors)->map(fn ($items) => array_values($items))->toArray();
                $this->createTable('Authors', ['Name', 'Email', 'Homepage'], $rows)?->render();
            }
        }
    }

    /**
     * @param  array  $dependencies
     * @param  array  $dev_dependencies
     * @return void
     */
    protected function displayDependenciesTable(array $dependencies = [], array $dev_dependencies = []): void
    {
        // Create a new TableSeparator instance.
        $separator = new TableSeparator();

        $get_displayable_dependencies = function (string $title, array $dependencies = []) use ($separator) {
            $title = $this->createTableCell($title, 'default-bold', 2);

            return collect($dependencies)->map(fn ($version, $package) => [$this->getBoldText($package, 'default'), $this->getBoldText($version)])
                ->prepend($separator)
                ->prepend([$title])
                ->toArray();
        };

        $dependencies = $get_displayable_dependencies('Required Dependencies', $dependencies);
        $dev_dependencies = $get_displayable_dependencies('Development Dependencies', $dev_dependencies);
        $rows = array_merge($dependencies, [$separator], $dev_dependencies);

        $this->createTable('Dependencies', ['Package', 'Version'], $rows)?->render();
    }

    /**
     * @param  array  $directories
     * @param  string|null  $package
     * @param  string|null  $domain
     * @return void
     */
    protected function displayDirectories(array $directories = [], string $package = null, string $domain = null): void
    {
        $directories = collect($directories);

        // Create a new TableSeparator instance.
        $separator = new TableSeparator();
        $yes = $this->createTableCell('YES', 'red-bold');
        $no = $this->createTableCell('NO');

        $rows = $directories->map(fn ($items, $key) => [$key, $items ? $items['path'] : null]);

        $this->createTable('Paths', ['Directory', 'Absolute Path'], $rows)?->render();

        $get_class_rows = function (Collection|array $items) {
            return collect($items)->map(fn ($name, $namespace) => [$name, $namespace]);
        };

        /***** MODELS & PROVIDERS *****/

        $display_classes = [];

        $headers = ['Name', 'Class'];

        $add_class_rows = function (string $target) use ($get_class_rows, $separator, &$display_classes, $directories, $headers) {
            if ($items = Arr::get($directories, $target.'.files')) {
                // add separator when not empty
                if (count($display_classes)) {
                    $display_classes[] = $separator;
                }

                $display_classes[] = [$this->createTableCell($target, 'default-bold', count($headers))];

                $display_classes = array_merge($display_classes, [$separator], $get_class_rows($items)->toArray());
            }
        };

        $add_class_rows(StarterKit::MODELS_DIR);
        $add_class_rows(StarterKit::PROVIDERS_DIR);

        $this->createTable('Models & Providers', $headers, $display_classes)?->render();

        /***** OBSERVERS, POLICIES, & REPOSITORIES *****/

        $observer_map = [];
        $policy_map = [];
        $repository_map = [];
        $display_model_related = [];

        $headers = ['Class', 'Model Class', 'Is Guessed?'];

        starterKit()->getProvidersFromList($package, $domain)->each(
            function (BaseStarterKitServiceProvider $provider) use (&$observer_map, &$policy_map, &$repository_map) {
                $observer_map = array_merge($observer_map, $provider->getObserverMap());
                $policy_map = array_merge($observer_map, $provider->getPolicyMap());
                $repository_map = array_merge($observer_map, $provider->getRepositoryMap());
            }
        );

        $add_model_related_rows = function (string $target, array $map) use ($yes, $no, $domain, $package, $separator, &$display_model_related, $headers) {
            $possible_models = match ($target) {
                StarterKit::OBSERVERS_DIR => starterKit()->getObservers($package, $domain, $map),
                StarterKit::POLICIES_DIR => starterKit()->getPolicies($package, $domain, $map),
                StarterKit::REPOSITORIES_DIR => starterKit()->getRepositories($package, $domain, $map),
            };

            $possible_models = $possible_models?->map(fn (Collection|array|string $related_model, $class) => [
                $class,
                is_string($related_model) ? $related_model : collect($related_model)->implode(', '),
                is_string($related_model) ? $no : $yes,
            ]);

            // add separator when not empty
            if (count($display_model_related)) {
                $display_model_related[] = $separator;
            }

            $display_model_related[] = [$this->createTableCell($target, 'default-bold', count($headers))];

            if ($possible_models?->count()) {
                $arr = $possible_models->toArray();
            } else {
                $arr = [[$this->createTableCell('No rows!', 'yellow-bold', count($headers))]];
            }

            $display_model_related = array_merge($display_model_related, [$separator], $arr);
        };

        $add_model_related_rows(StarterKit::OBSERVERS_DIR, $observer_map);
        $add_model_related_rows(StarterKit::POLICIES_DIR, $policy_map);
        $add_model_related_rows(StarterKit::REPOSITORIES_DIR, $repository_map);

        $this->createTable('Model Related Classes', $headers, $display_model_related)?->render();

        /***** OTHER LARAVEL FILES *****/

        $display_others = [];

        $headers = ['File', 'Absolute Path', 'Name'];

        $add_other_rows = function (string $target) use ($separator, &$display_others, $directories, $headers) {
            if ($items = Arr::get($directories, $target.'.files')) {
                // add separator when not empty
                if (count($display_others)) {
                    $display_others[] = $separator;
                }

                $display_others[] = [$this->createTableCell($target, 'default-bold', count($headers))];

                $display_others = array_merge($display_others, [$separator], $items->toArray());
            }
        };

        $add_other_rows(StarterKit::ROUTES_DIR);
        $add_other_rows(StarterKit::CONFIG_DIR);
        $add_other_rows(StarterKit::HELPERS_DIR);

        $this->createTable('Other Laravel Files', $headers, $display_others)?->render();
    }
}
