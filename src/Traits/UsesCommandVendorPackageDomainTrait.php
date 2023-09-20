<?php

namespace Luchavez\BoilerplateGenerator\Traits;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use Luchavez\BoilerplateGenerator\Console\Commands\PackageCloneCommand;
use Luchavez\BoilerplateGenerator\Console\Commands\PackageCreateCommand;
use Luchavez\BoilerplateGenerator\Exceptions\MissingNameArgumentException;
use Luchavez\BoilerplateGenerator\Exceptions\PackageNotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Trait UsesCommandVendorPackageDomainTrait
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 *
 * @since  2021-11-11
 */
trait UsesCommandVendorPackageDomainTrait
{
    use UsesCommandDomainTrait;

    /***** PACKAGE RELATED FIELDS *****/

    /**
     * @var string|null
     */
    protected ?string $package_option_argument_name = 'package';

    /**
     * @var string|null
     */
    protected ?string $package_name = null;

    /**
     * @var string|null
     */
    protected ?string $vendor_name = null;

    /**
     * @var string|null
     */
    protected ?string $package_name_studly = null;

    /**
     * @var string|null
     */
    protected ?string $vendor_name_studly = null;

    /**
     * @var string|null
     */
    protected ?string $package_namespace = null;

    /**
     * @var bool
     */
    protected bool $is_package_namespace_disabled = false;

    /**
     * @var string|null
     */
    protected ?string $package_dir = null;

    /**
     * @var string
     */
    protected string $default_package = 'none';

    /**
     * @var bool
     */
    protected bool $is_package_argument = false;

    /***** OTHER FIELDS *****/

    /**
     * @var Collection|null
     */
    protected ?Collection $moreReplaceNamespace = null;

    /**
     * @param  bool  $has_force
     * @param  bool  $has_domain_choices
     * @param  bool  $has_force_domain
     * @param  string  $option_name
     * @return void
     */
    public function addPackageDomainOptions(
        bool $has_force = false,
        bool $has_domain_choices = true,
        bool $has_force_domain = true,
        string $option_name = 'package',
    ): void {
        $this->package_option_argument_name = $option_name;

        $this->getDefinition()->addOption(
            new InputOption(
                $this->package_option_argument_name,
                null,
                InputOption::VALUE_OPTIONAL,
                'Target package to generate the files (e.g., `vendor-name/package-name`).'
            )
        );

        // Add domain options
        if ($has_domain_choices) {
            $this->addDomainOptions($has_force_domain);
        }

        if ($has_force && $this->getDefinition()->hasOption('force') === false) {
            $this->getDefinition()->addOption(
                new InputOption(
                    'force',
                    'f',
                    InputOption::VALUE_NONE,
                    'Overwrite file if exists.'
                )
            );
        }

        $this->is_package_argument = false;
    }

    /**
     * @param  bool  $isRequired
     * @param  string  $argument_name
     * @return void
     */
    public function addPackageArguments(bool $isRequired = true, string $argument_name = 'package'): void
    {
        $this->package_option_argument_name = $argument_name;

        $mode = $isRequired ? InputArgument::REQUIRED : InputArgument::OPTIONAL;

        $this->getDefinition()->addArguments(
            [
                new InputArgument($this->package_option_argument_name, $mode, 'The name of the package, e.g., `vendor-name/package-name`.'),
            ]
        );

        $this->is_package_argument = true;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments(): array
    {
        if ($this->isGeneratorSubclass()) {
            return [
                ['name', InputArgument::OPTIONAL, 'The name of the class'],
            ];
        }

        return parent::getArguments();
    }

    /**
     * @param  bool  $show_package_choices
     * @param  bool  $show_domain_choices
     * @param  bool  $show_default_package
     * @param  array|string|null  $filter
     * @param  bool  $is_local
     * @param  bool|null  $is_enabled
     * @param  bool|null  $is_loaded
     * @return void
     *
     * @throws MissingNameArgumentException
     * @throws PackageNotFoundException
     */
    public function setVendorPackageDomain(
        bool $show_package_choices = true,
        bool $show_domain_choices = true,
        bool $show_default_package = true,
        array|string $filter = null,
        bool $is_local = true,
        bool $is_enabled = null,
        bool $is_loaded = null
    ): void {
        // Set Author Information
        $this->setAuthorInformationOnStub();

        if ($this->isGeneratorSubclass()) {
            $name = $this->getNameInput();
            $this->ongoing('Creating '.$this->type.($name ? ': '.$name : null));
        }

        $package = $this->getPackageFromOptions() ?: $this->getPackageFromArguments();

        if (! $package && $show_package_choices) {
            $package = $this->choosePackageFromList(
                filter: $filter,
                is_local: $is_local,
                is_enabled: $is_enabled,
                is_loaded: $is_loaded,
                show_default_package: $show_default_package
            );
        }

        if ($package === $this->default_package) {
            $package = null;
        }

        if ($package && str_contains($package, '/')) {
            [$this->vendor_name, $this->package_name] = explode('/', $package);

            if ($this->vendor_name && $this->package_name) {
                // Formatting
                $this->vendor_name = Str::kebab($this->vendor_name);
                $this->package_name = Str::kebab($this->package_name);
                $this->vendor_name_studly = Str::studly($this->vendor_name);
                $this->package_name_studly = Str::studly($this->package_name);
                $this->package_dir = $this->vendor_name.'/'.$this->package_name;
                $this->package_namespace = $this->vendor_name_studly.'\\'.$this->package_name_studly.'\\';

                // Check if folder exists
                if (
                    ! $this instanceof PackageCreateCommand &&
                    ! $this instanceof PackageCloneCommand &&
                    ! file_exists(package_domain_path($this->package_dir))
                ) {
                    if ($this->isNoInteraction()) {
                        throw new PackageNotFoundException($this->package_dir);
                    }

                    $this->error('Package not found! Please choose an existing package.');

                    if ($this->is_package_argument) {
                        $this->input->setArgument($this->package_option_argument_name, null);
                    } else {
                        $this->input->setOption($this->package_option_argument_name, null);
                    }

                    $this->setVendorPackageDomain();
                }
            }
        }

        if ($show_domain_choices) {
            $this->setDomainFieldsFromOptions(
                $this->package_option_argument_name,
                $this->package_dir,
                $this->package_namespace
            );
        }
    }

    /**
     * @return bool
     */
    public function hasPackageAsOption(): bool
    {
        return $this->hasOption($this->package_option_argument_name);
    }

    /**
     * @param  bool  $multiple
     * @return string|array|null
     */
    public function getPackageFromOptions(bool $multiple = false): string|array|null
    {
        if ($this->hasPackageAsOption() &&
            $target = trim($this->option($this->package_option_argument_name), '/')) {
            return $multiple ? explode(',', $target) : $target;
        }

        return null;
    }

    /**
     * @return bool
     */
    public function hasPackageAsArgument(): bool
    {
        return $this->hasArgument($this->package_option_argument_name);
    }

    /**
     * @return string|null
     */
    public function getPackageFromArguments(): ?string
    {
        return $this->hasPackageAsArgument() ?
            trim($this->argument($this->package_option_argument_name), '/') :
            null;
    }

    /**
     * @param  string|array|null  $filter
     * @param  bool|null  $is_local
     * @param  bool|null  $is_enabled
     * @param  bool|null  $is_loaded
     * @param  bool  $show_default_package
     * @param  bool  $multiple
     * @param  array  $default_choices
     * @return array|string|null
     */
    public function choosePackageFromList(
        string|array $filter = null,
        ?bool $is_local = true,
        bool $is_enabled = null,
        bool $is_loaded = null,
        bool $show_default_package = true,
        bool $multiple = false,
        array $default_choices = []
    ): array|string|null {
        $choices = boilerplateGenerator()->getSummarizedPackages($filter, $is_local, $is_enabled, $is_loaded)->keys();

        if ($choices->count()) {
            $choices = $choices->when($show_default_package, fn ($choices) => $choices->prepend($this->default_package));

            if ($show_default_package && ! count($default_choices)) {
                $default_choices[] = $this->default_package;
            }

            $default = null;

            if (count($default_choices)) {
                $default = collect($default_choices)->filter(fn ($item) => collect()->doesntContain($item));
            }

            if ($multiple) {
                return multiselect(label: 'Choose target packages', options: $choices, default: $default);
            }

            return select(label: 'Choose target package', options: $choices, default: $default->first());
        }

        return null;
    }

    /**
     * @param  bool  $with_domain
     * @return array
     */
    public function getPackageArgs(bool $with_domain = true): array
    {
        $args['--'.$this->package_option_argument_name] = $this->package_dir ?? $this->default_package;

        if ($with_domain) {
            $args = array_merge($args, $this->getDomainArgs());
        }

        return $args;
    }

    /**
     * @return string
     */
    protected function rootNamespace(): string
    {
        if ($this->is_package_namespace_disabled || ! $namespace = $this->getPackageDomainNamespace()) {
            return parent::rootNamespace();
        }

        return $namespace;
    }

    /***** NAME INPUT *****/

    /**
     * Class type to append on filename.
     *
     * @return string|null
     */
    abstract protected function getClassType(): ?string;

    /**
     * Get the validated desired class name from the input.
     *
     * @return string
     */
    protected function getValidatedNameInput(): string
    {
        $classType = $this->getClassType();
        $name = trim($this->argument('name'));

        if ($classType) {
            return Str::of($name)->before($classType).$classType;
        }

        return $name;
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string|null
     *
     * @throws MissingNameArgumentException
     */
    protected function getNameInput(): ?string
    {
        if ($this->isGeneratorSubclass()) {
            if (is_null($this->argument('name'))) {
                if ($this->isNoInteraction()) {
                    throw new MissingNameArgumentException();
                }

                $type = Str::lower($this->type) ?? 'file';

                $this->input->setArgument('name', text("What is the name of the $type?"));

                return $this->getNameInput();
            }

            return $this->getValidatedNameInput();
        }

        return trim($this->argument('name')) ?? '';
    }

    /**
     * @return bool
     */
    public function isNoInteraction(): bool
    {
        return $this->option('no-interaction');
    }

    /**
     * @return bool
     */
    public function isGeneratorSubclass(): bool
    {
        return is_subclass_of($this, GeneratorCommand::class);
    }

    /***** PACKAGE LIST *****/

    /**
     * @return string|null
     */
    protected function getPackageDomainNamespace(): ?string
    {
        return $this->domain_namespace ?? $this->package_namespace;
    }

    /**
     * @return string
     */
    protected function getPackageDomainFullPath(): string
    {
        return package_domain_app_path($this->package_dir, $this->domain_dir);
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name): string
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        $path = $this->getPackageDomainFullPath();

        return $path.DIRECTORY_SEPARATOR.str_replace('\\', '/', $name).'.php';
    }

    /***** STUB REPLACEMENT LOGIC *****/

    /**
     * @param  Collection|array  $more
     * @return Collection
     */
    public function addMoreReplaceNamespace(Collection|array $more): Collection
    {
        if (count($more)) {
            if (! $this->moreReplaceNamespace) {
                $this->moreReplaceNamespace = collect($more);
            } else {
                $this->moreReplaceNamespace = $this->moreReplaceNamespace->merge($more);
            }
        }

        return $this->moreReplaceNamespace ?? collect();
    }

    /**
     * @param  string  $namespacedClass
     * @param  string|null  $classType
     * @return Collection
     */
    protected function addMoreCasedReplaceNamespace(string $namespacedClass, string $classType = null): Collection
    {
        $class = Str::of($namespacedClass)->afterLast('\\')->studly();

        $key = $classType ?? $class->jsonSerialize();

        $more = collect(
            [
                'Namespaced'.$key => $namespacedClass,
                $key.'Class' => $class,
                $key.'Snake' => $class->snake(),
                $key.'Slug' => $class->snake('-'),
                $key.'Camel' => $class->camel(),
            ]
        );

        $this->addMoreReplaceNamespace($more);

        return $more;
    }

    /**
     * Overriding to inject more namespace.
     * Replace the namespace for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return $this
     */
    protected function replaceNamespace(&$stub, $name): static
    {
        $searches = [
            ['DummyNamespace', 'DummyRootNamespace', 'NamespacedDummyUserModel'],
            ['{{ namespace }}', '{{ rootNamespace }}', '{{ namespacedUserModel }}'],
            ['{{namespace}}', '{{rootNamespace}}', '{{namespacedUserModel}}'],
        ];

        $replacements = [
            $this->getNamespace($name),
            $this->rootNamespace(),
            $this->userProviderModel(),
        ];

        if ($this->moreReplaceNamespace && Arr::isAssoc($this->moreReplaceNamespace->toArray())) {
            $this->moreReplaceNamespace->each(
                function ($item, $key) use (&$searches, &$replacements) {
                    $item = trim($item);
                    $key = trim($key);

                    if ($item && $key) {
                        $searches[0][] = Str::studly($key);
                        $searches[1][] = '{{ '.Str::camel($key).' }}';
                        $searches[2][] = '{{'.Str::camel($key).'}}';
                        $replacements[] = $item;
                    }
                }
            );
        }

        foreach ($searches as $search) {
            $stub = str_replace(
                $search,
                $replacements,
                $stub
            );
        }

        return $this;
    }

    /**
     * @param  string  $classNamespace
     * @return array|string
     */
    protected function cleanClassNamespace(string $classNamespace): array|string
    {
        $classNamespace = ltrim($classNamespace, '\\/');

        return str_replace('/', '\\', $classNamespace);
    }

    /**
     * @return bool
     */
    protected function shouldOverwrite(): bool
    {
        return $this->hasOption('force') && $this->option('force');
    }

    /***** MODEL RELATED *****/

    /**
     * @param  string  $option_name
     * @return string
     */
    protected function getModelClass(string $option_name): string
    {
        $model = $this->option($option_name);
        $model_class = $model ? $this->parseModel($model) : null;

        if (! $model_class || ! class_exists($model_class)) {
            // ask if the model should be generated instead
            if ($model_class && confirm(label: "$model_class model does not exist. Do you want to generate it?")) {
                $args = $this->getPackageArgs();
                $args['name'] = $model_class;

                $this->call('bg:make:model', $args);
            }

            // or maybe choose from possible Eloquent models
            else {
                $possible_models = starterKit()->getPossibleModels($this->package_dir, $this->domain_name)->collapse();

                $model_class = select(
                    label: 'Choose alternative '.($option_name === 'parent' ? $option_name.' ' : null).'model',
                    options: $possible_models,
                );

                $this->input->setOption($option_name, $model_class);
            }
        }

        return $model_class;
    }

    /**
     * Get the fully-qualified model class name.
     *
     * @param  string  $model
     * @return string
     *
     * @throws InvalidArgumentException
     */
    protected function parseModel($model): string
    {
        if (preg_match('([^A-Za-z\d_/\\\\])', $model)) {
            throw new InvalidArgumentException('Model name contains invalid characters.');
        }

        return $this->qualifyModel($model);
    }

    /***** TEST RELATED *****/

    /**
     * Create the matching test case if requested.
     *
     * @param  string  $path
     * @return void
     */
    protected function handleTestCreation($path): void
    {
        $app_path = package_domain_app_path($this->package_dir, $this->domain_dir);

        $name = Str::of($path)
            ->replace('\\', '/')
            ->after($app_path)
            ->beforeLast('.php');

        $args['name'] = $name->append('Test')
            ->ltrim('/')
            ->jsonSerialize();

        $args['--no-interaction'] = true;
        $args['--pest'] = $this->option('pest');
        $args = array_merge($args, $this->getPackageArgs());

        $this->call('bg:make:test', $args);
    }

    /***** AUTHOR INFORMATION FOR FILE GENERATION *****/

    /**
     * @return void
     */
    public function setAuthorInformationOnStub(): void
    {
        $this->addMoreReplaceNamespace(
            [
                'authorName' => boilerplateGenerator()->getAuthorName(),
                'authorEmail' => boilerplateGenerator()->getAuthorEmail(),
            ]
        );
    }
}
