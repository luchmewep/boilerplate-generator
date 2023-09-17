<?php

namespace Luchavez\BoilerplateGenerator\Console\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Console\ConsoleMakeCommand;
use Luchavez\BoilerplateGenerator\Exceptions\MissingNameArgumentException;
use Luchavez\BoilerplateGenerator\Exceptions\PackageNotFoundException;
use Luchavez\BoilerplateGenerator\Traits\UsesCommandVendorPackageDomainTrait;

/**
 * Class ExtendedMakeCommand
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 *
 * @since  2021-11-19
 */
class ExtendedMakeCommand extends ConsoleMakeCommand
{
    use UsesCommandVendorPackageDomainTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'bg:make:command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Artisan command in Laravel or in a specific package.';

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

    /***** OVERRIDDEN FUNCTIONS *****/

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
        return __DIR__.'/../../../stubs/command/command.custom.stub';
    }

    /**
     * Class type to append on filename.
     *
     * @return string|null
     */
    protected function getClassType(): ?string
    {
        return 'Command';
    }
}
