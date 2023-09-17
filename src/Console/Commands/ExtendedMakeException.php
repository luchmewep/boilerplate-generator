<?php

namespace Luchavez\BoilerplateGenerator\Console\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Console\ExceptionMakeCommand;
use Luchavez\BoilerplateGenerator\Exceptions\MissingNameArgumentException;
use Luchavez\BoilerplateGenerator\Exceptions\PackageNotFoundException;
use Luchavez\BoilerplateGenerator\Traits\UsesCommandVendorPackageDomainTrait;

/**
 * Class ExtendedMakeException
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 *
 * @since  2021-11-20
 */
class ExtendedMakeException extends ExceptionMakeCommand
{
    use UsesCommandVendorPackageDomainTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'bg:make:exception';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new custom exception class in Laravel or in a specific package.';

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
        $exceptionRenderReportStub = __DIR__.'/../../../stubs/exception/exception-render-report.custom.stub';
        $exceptionRenderStub = __DIR__.'/../../../stubs/exception/exception-render.custom.stub';
        $exceptionReportStub = __DIR__.'/../../../stubs/exception/exception-report.custom.stub';
        $exceptionStub = __DIR__.'/../../../stubs/exception/exception.custom.stub';

        if (file_exists($exceptionRenderReportStub) === false ||
            file_exists($exceptionRenderStub) === false ||
            file_exists($exceptionReportStub) === false ||
            file_exists($exceptionStub) === false
        ) {
            return parent::getStub();
        }

        if ($this->option('render')) {
            return $this->option('report')
                ? $exceptionRenderReportStub
                : $exceptionRenderStub;
        }

        return $this->option('report')
            ? $exceptionReportStub
            : $exceptionStub;
    }

    /**
     * Class type to append on filename.
     *
     * @return string|null
     */
    protected function getClassType(): ?string
    {
        return 'Exception';
    }
}
