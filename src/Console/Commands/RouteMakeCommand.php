<?php

namespace Luchavez\BoilerplateGenerator\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Luchavez\BoilerplateGenerator\Exceptions\MissingNameArgumentException;
use Luchavez\BoilerplateGenerator\Exceptions\PackageNotFoundException;
use Luchavez\BoilerplateGenerator\Traits\UsesCommandVendorPackageDomainTrait;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class RouteMakeCommand
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 *
 * @since  2021-11-25
 */
class RouteMakeCommand extends GeneratorCommand
{
    use UsesCommandVendorPackageDomainTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'bg:make:route';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new route file in Laravel or in a specific package.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Route';

    /*****
     * OVERRIDDEN FUNCTIONS
     *****/

    /**
     * Create a new controller creator command instance.
     *
     * @param  Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct($files);

        $this->addPackageDomainOptions(true);
    }

    /**
     * @throws PackageNotFoundException|MissingNameArgumentException|FileNotFoundException
     */
    public function handle(): bool|int|null
    {
        $this->setVendorPackageDomain();

        $name = $this->getNameInput();

        $path = $this->getPath($name);

        if (! $this->shouldOverwrite() && file_exists($path)) {
            $this->warning(Str::ucfirst($name).' route already exists!');

            return self::FAILURE;
        }

        // Next, we will generate the path to the location where this class' file should get
        // written. Then, we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.
        $this->makeDirectory($path);

        $this->files->put($path, $this->sortImports($this->buildClass($name)));

        $this->info($this->type.' created successfully.');

        return self::SUCCESS;
    }

    /**
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['api', null, InputOption::VALUE_NONE, 'Generate api route.'],
        ];
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub(): string
    {
        return __DIR__.'/../../../stubs/route/route.custom.stub';
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
     * Get the validated desired class name from the input.
     *
     * @return string
     *
     * @throws MissingNameArgumentException
     */
    protected function getValidatedNameInput(): string
    {
        $name = Str::of($this->argument('name'))->replace('\\', '/');

        $file = $name->afterLast('/')->jsonSerialize();
        $dir = $name->beforeLast($file)->trim('/')->jsonSerialize();

        $file = Str::of(preg_replace('/[^a-z\d-]/i', '-', $file))
            ->lower()
            ->replace('api', null)
            ->trim('-')
            ->when($this->option('api'), fn (Stringable $str) => $str->append('.api'))
            ->trim('.');

        if ($file->isNotEmpty()) {
            return collect([$dir, $file])->filter()->implode('/');
        }

        // go back to getNameInput() when empty
        $this->input->setArgument('name', null);

        return $this->getNameInput();
    }

    /**
     * @return string
     */
    protected function getPackageDomainFullPath(): string
    {
        if ($this->package_dir) {
            return package_domain_routes_path($this->package_dir, $this->domain_dir);
        }

        return str_replace('\\', '/', base_path($this->domain_dir)).'/routes';
    }
}
