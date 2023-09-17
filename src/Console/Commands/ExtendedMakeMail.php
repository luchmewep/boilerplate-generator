<?php

namespace Luchavez\BoilerplateGenerator\Console\Commands;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Console\MailMakeCommand;
use Luchavez\BoilerplateGenerator\Exceptions\MissingNameArgumentException;
use Luchavez\BoilerplateGenerator\Exceptions\PackageNotFoundException;
use Luchavez\BoilerplateGenerator\Traits\UsesCommandVendorPackageDomainTrait;

/**
 * Class ExtendedMakeMail
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 *
 * @since  2021-11-20
 */
class ExtendedMakeMail extends MailMakeCommand
{
    use UsesCommandVendorPackageDomainTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'bg:make:mail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new email class in Laravel or in a specific package.';

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
     * @return void
     *
     * @throws PackageNotFoundException|MissingNameArgumentException
     */
    public function handle(): void
    {
        $this->setVendorPackageDomain();

        parent::handle();
    }

    /**
     * @return string
     */
    protected function getStub(): string
    {
        return $this->option('markdown') !== false ? __DIR__.'/../../../stubs/mail/mail-markdown.custom.stub' :
            __DIR__.'/../../../stubs/mail/mail.custom.stub';
    }

    /**
     * Class type to append on filename.
     *
     * @return string|null
     */
    protected function getClassType(): ?string
    {
        return 'Mail';
    }
}
