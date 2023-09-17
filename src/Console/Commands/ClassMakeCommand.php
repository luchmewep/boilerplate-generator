<?php

namespace Luchavez\BoilerplateGenerator\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Luchavez\BoilerplateGenerator\Exceptions\MissingNameArgumentException;
use Luchavez\BoilerplateGenerator\Exceptions\PackageNotFoundException;
use Luchavez\BoilerplateGenerator\Traits\UsesCommandVendorPackageDomainTrait;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ClassMakeCommand
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 *
 * @since  2021-11-10
 */
class ClassMakeCommand extends GeneratorCommand
{
    use UsesCommandVendorPackageDomainTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'bg:make:class';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new PHP class in Laravel or in a specific package.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Class';

    public function __construct(Filesystem $files)
    {
        parent::__construct($files);

        $this->addPackageDomainOptions();

        if ($this->getDefinition()->hasOption('abstract') === false) {
            $this->getDefinition()->addOptions(
                [
                    new InputOption(
                        'abstract',
                        'a',
                        InputOption::VALUE_NONE,
                        'Generate an abstract class.'
                    ),
                    new InputOption(
                        'invokable',
                        'i',
                        InputOption::VALUE_NONE,
                        'Generate a single method, invokable class.'
                    ),
                ]
            );
        }
    }

    /*****
     * OVERRIDDEN FUNCTIONS
     *****/

    /**
     * @return bool|null
     *
     * @throws FileNotFoundException
     * @throws PackageNotFoundException|MissingNameArgumentException
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
        $type = '';

        if ($this->option('abstract')) {
            $type = '.abstract';
        } elseif ($this->option('invokable')) {
            $type = '.invokable';
        }

        return __DIR__.'/../../../stubs/class/class'.$type.'.custom.stub';
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
}
