<?php

namespace Luchavez\BoilerplateGenerator\Console\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\Console\ControllerMakeCommand;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;
use Luchavez\BoilerplateGenerator\Exceptions\MissingNameArgumentException;
use Luchavez\BoilerplateGenerator\Exceptions\PackageNotFoundException;
use Luchavez\BoilerplateGenerator\Traits\UsesCommandVendorPackageDomainTrait;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ExtendedMakeController
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 *
 * @since  2021-11-20
 */
class ExtendedMakeController extends ControllerMakeCommand
{
    use UsesCommandVendorPackageDomainTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'bg:make:controller';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new controller class in Laravel or in a specific package.';

    /**
     * Controller Commands
     *
     * @var array|string[]
     */
    public array $controllerMethods = [
        'Index' => 'Collected',
        'Store' => 'Created',
        'Show' => 'Shown',
        'Update' => 'Updated',
        'Delete' => 'Archived',
    ];

    /**
     * Create a new controller creator command instance.
     *
     * @param  Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct($files);

        $this->addPackageDomainOptions();
    }

    /**
     * @param  bool  $isModelRestorable
     * @return Collection
     */
    public function getControllerMethods(bool $isModelRestorable = true): Collection
    {
        $collection = collect($this->controllerMethods);

        if ($isModelRestorable) {
            return $collection->put('Restore', 'Restored');
        }

        return $collection;
    }

    /*****
     * OVERRIDDEN FUNCTIONS
     *****/

    /**
     * @return bool|null
     *
     * @throws FileNotFoundException|PackageNotFoundException|MissingNameArgumentException
     */
    public function handle(): ?bool
    {
        $this->setVendorPackageDomain();

        return parent::handle();
    }

    /**
     * @return string
     */
    protected function getStub(): string
    {
        $stub = null;

        if ($type = $this->option('type')) {
            $stub = __DIR__."/stubs/controller/controller.$type.stub";
        } elseif ($this->option('parent')) {
            $stub = __DIR__.'/../../../stubs/controller/controller.nested.custom.stub';
        } elseif ($this->option('model')) {
            $stub = __DIR__.'/../../../stubs/controller/controller.model.custom.stub';
        } elseif ($this->option('invokable')) {
            $stub = __DIR__.'/../../../stubs/controller/controller.invokable.custom.stub';
        } elseif ($this->option('resource')) {
            $stub = __DIR__.'/../../../stubs/controller/controller.custom.stub';
        }

        if ($this->option('api')) {
            if (is_null($stub)) {
                $stub = __DIR__.'/../../../stubs/controller/controller.api.custom.stub';
            } elseif (! $this->option('invokable')) {
                $stub = str_replace('.custom.stub', '.api.custom.stub', $stub);
            }
        }

        $stub = $stub ?? __DIR__.'/../../../stubs/controller/controller.plain.custom.stub';

        if (file_exists($stub) === false) {
            return parent::getStub();
        }

        return $stub;
    }

    /**
     * Build the replacements for a parent controller.
     *
     * @return array
     */
    #[ArrayShape(
        [
            'ParentDummyFullModelClass' => 'string',
            '{{ namespacedParentModel }}' => 'string',
            '{{namespacedParentModel}}' => 'string',
            'ParentDummyModelClass' => 'string',
            '{{ parentModel }}' => 'string',
            '{{parentModel}}' => 'string',
            'ParentDummyModelVariable' => 'string',
            '{{ parentModelVariable }}' => 'string',
            '{{parentModelVariable}}' => 'string',
        ]
    )]
    protected function buildParentReplacements(): array
    {
        $parentModelClass = $this->getModelClass('parent');

        return [
            'ParentDummyFullModelClass' => $parentModelClass,
            '{{ namespacedParentModel }}' => $parentModelClass,
            '{{namespacedParentModel}}' => $parentModelClass,
            'ParentDummyModelClass' => class_basename($parentModelClass),
            '{{ parentModel }}' => class_basename($parentModelClass),
            '{{parentModel}}' => class_basename($parentModelClass),
            'ParentDummyModelVariable' => lcfirst(class_basename($parentModelClass)),
            '{{ parentModelVariable }}' => lcfirst(class_basename($parentModelClass)),
            '{{parentModelVariable}}' => lcfirst(class_basename($parentModelClass)),
        ];
    }

    /**
     * Build the model replacement values.
     *
     * @param  array  $replace
     * @return array
     */
    protected function buildModelReplacements(array $replace): array
    {
        $modelClass = $this->getModelClass('model');

        $replaceModelNamespaces = [];

        if ($this->option('model')) {
            $replace = $this->buildFormRequestReplacements($replace, $modelClass);
            $replaceModelNamespaces = [
                'DummyFullModelClass' => $modelClass,
                '{{ namespacedModel }}' => $modelClass,
                '{{namespacedModel}}' => $modelClass,
                'DummyModelClass' => class_basename($modelClass),
                '{{ model }}' => class_basename($modelClass),
                '{{model}}' => class_basename($modelClass),
                'DummyModelVariable' => lcfirst(class_basename($modelClass)),
                '{{ modelVariable }}' => lcfirst(class_basename($modelClass)),
                '{{modelVariable}}' => lcfirst(class_basename($modelClass)),
            ];
        }

        return array_merge($replace, $replaceModelNamespaces);
    }

    /**
     * Build the model replacement values.
     *
     * @param  array  $replace
     * @param  string  $modelClass
     * @return array
     */
    protected function buildFormRequestReplacements(array $replace, $modelClass): array
    {
        if ($modelClass) {
            $res = collect();

            $model = Str::of($modelClass)->afterLast('\\');
            $this->getControllerMethods()->each(
                function ($event, $request) use ($modelClass, $model, $res) {
                    // Generate Request
                    $requestClass = $request.$model.'Request';
                    $requestClassPath = $model.'\\'.$requestClass;
                    $namespacedRequestClass = $this->rootNamespace().'Http\\Requests\\'.$requestClassPath;

                    $requestArgs = $this->getPackageArgs();
                    $requestArgs['name'] = $requestClassPath;

                    $this->call('bg:make:request', $requestArgs);

                    $res->put('{{ '.Str::camel($request.'Request').' }}', $requestClass);
                    $res->put('{{'.Str::camel($request.'Request').'}}', $requestClass);
                    $res->put('{{ '.Str::camel('namespaced'.$request.'Request').' }}', $namespacedRequestClass);
                    $res->put('{{'.Str::camel('namespaced'.$request.'Request').'}}', $namespacedRequestClass);

                    // Generate Event
                    $eventClass = $model.$event.'Event';
                    $eventClassPath = $model.'\\'.$eventClass;
                    $namespacedEventClass = $this->rootNamespace().'Events\\'.$eventClassPath;

                    $eventArgs = $this->getPackageArgs();
                    $eventArgs['name'] = $eventClassPath;
                    $eventArgs['--model'] = $modelClass;
                    $this->call('bg:make:event', $eventArgs);

                    $res->put('{{ '.Str::camel($request.'Event').' }}', $eventClass);
                    $res->put('{{'.Str::camel($request.'Event').'}}', $eventClass);
                    $res->put('{{ '.Str::camel('namespaced'.$request.'Event').' }}', $namespacedEventClass);
                    $res->put('{{'.Str::camel('namespaced'.$request.'Event').'}}', $namespacedEventClass);
                }
            );

            return array_merge($replace, $res->toArray());
        }

        return [];
    }

    /**
     * @return array
     */
    protected function getOptions(): array
    {
        $options = collect(parent::getOptions())
            ->filter(fn ($value) => ! collect($value)->contains('requests'))
            ->toArray();

        return array_merge(
            $options,
            [
                ['repo', null, InputOption::VALUE_NONE, 'Create new repository class based on the model.'],
                ['skip-model', null, InputOption::VALUE_NONE, 'Proceed as if model is already created.'],
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
        return 'Controller';
    }
}
