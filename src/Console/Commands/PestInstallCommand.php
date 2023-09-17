<?php

namespace Luchavez\BoilerplateGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Luchavez\BoilerplateGenerator\Exceptions\MissingNameArgumentException;
use Luchavez\BoilerplateGenerator\Exceptions\PackageNotFoundException;
use Luchavez\BoilerplateGenerator\Traits\UsesCommandVendorPackageDomainTrait;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class PestInstallCommand
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 */
class PestInstallCommand extends Command
{
    use UsesCommandVendorPackageDomainTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'bg:pest:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates Pest resources in your current PHPUnit test suite';

    /**
     * Create a new console command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->addPackageDomainOptions();
    }

    /**
     * Execute the console command.
     *
     * @return int
     *
     * @throws PackageNotFoundException|MissingNameArgumentException
     */
    public function handle(): int
    {
        $this->setVendorPackageDomain();

        $file = 'Pest.php';

        $target = package_domain_tests_path($this->package_dir, $this->domain_dir);

        $source = __DIR__.'/../../../stubs/pest';

        if ($this->option('force') || file_exists($target.'/'.$file) === false) {
            File::copyDirectory($source, $target);

            $this->info('Pest installed successfully.');

            return self::SUCCESS;
        }

        $this->warn('Pest installed already!');

        return self::FAILURE;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Force create Gitlab CI yml file.'],
        ];
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
