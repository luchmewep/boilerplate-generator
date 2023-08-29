<?php

namespace Luchavez\BoilerplateGenerator\Console\Commands;

use Luchavez\BoilerplateGenerator\Exceptions\MissingNameArgumentException;
use Luchavez\BoilerplateGenerator\Exceptions\PackageNotFoundException;
use Luchavez\BoilerplateGenerator\Traits\UsesCommandEloquentModelTrait;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Console\TestMakeCommand;
use Illuminate\Support\Str;

/**
 * Class ExtendedMakeTest
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 *
 * @since  2021-11-10
 */
class ExtendedMakeTest extends TestMakeCommand
{
    use UsesCommandEloquentModelTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'bg:make:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new test class in Laravel or in a specific package.';

    /*****
     * OVERRIDDEN FUNCTIONS
     *****/

    /**
     * Override Constructor to add model option.
     *
     * @param  Filesystem  $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct($files);

        $this->addModelOptions();

        $this->addPackageDomainOptions();
    }

    /**
     * @return bool|null
     *
     * @throws FileNotFoundException|PackageNotFoundException|MissingNameArgumentException
     */
    public function handle(): ?bool
    {
        $this->setVendorPackageDomain();

        $this->setModelFields();

        $name = Str::of($this->getValidatedNameInput());

        if ($this->option('pest') || boilerplateGenerator()->isPestEnabled()) {
            $path = package_domain_tests_path($this->package_dir, $this->domain_dir);

            // Create tests folder if not exists.
            if (! file_exists($path)) {
                mkdir($path);
            }

            $args['--test-directory'] = Str::of($path)
                ->after(base_path())
                ->replace('\\', '/')
                ->ltrim('/')
                ->jsonSerialize();

            // Check if Pest is already installed.
            // If not, install it.
            $file_location = guess_file_or_directory_path(
                package_domain_tests_path($this->package_dir, $this->domain_dir),
                'Pest.php'
            );

            if (is_null($file_location)) {
                $this->call('bg:pest:install', array_merge($args, [
                    '--package' => $this->package_dir,
                    '--domain' => $this->domain_name,
                    '--no-interaction' => true
                ]));
            }

            // Sync options with args
            $args = collect($this->options())->only(['unit', 'force', 'verbose', 'env'])
                ->mapWithKeys(fn($item, $key) => ["--$key" => $item])
                ->merge($args);

            // Generate Pest Test
            $args->put('name', $name->jsonSerialize());
            $this->call('pest:test', $args->toArray());

            // Generate Dataset
            $args->put('name', $name
                ->replace('\\', '/')
                ->afterLast('/')
                ->before('Test')
                ->jsonSerialize());

            $args->forget(['--unit', '--force']);

            $result = $this->call('pest:dataset', $args->toArray());
        } else {
            $result = parent::handle();
        }

        return $result == self::SUCCESS;
    }

    /**
     * @return string
     */
    protected function getStub(): string
    {
        return __DIR__.'/../../../stubs/test/test'.($this->option('unit') ? '.unit' : null).
            ($this->option('model') ? '.model' : null).'.custom.stub';
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

        $path = package_domain_tests_path($this->package_dir, $this->domain_dir);

        return $path.DIRECTORY_SEPARATOR.str_replace('\\', '/', $name).'.php';
    }

    /**
     * Class type to append on filename.
     *
     * @return string|null
     */
    protected function getClassType(): ?string
    {
        return 'Test';
    }
}
