<?php

namespace Luchavez\BoilerplateGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Luchavez\BoilerplateGenerator\Exceptions\MissingNameArgumentException;
use Luchavez\BoilerplateGenerator\Exceptions\PackageNotFoundException;
use Luchavez\BoilerplateGenerator\Traits\UsesCommandVendorPackageDomainTrait;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class GitlabCIMakeCommand
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 */
class GitlabCIMakeCommand extends Command
{
    use UsesCommandVendorPackageDomainTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'bg:gitlab:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Gitlab CI YML file in a specific package.';

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

        $file = '.gitlab-ci.yml';

        $packagePath = package_domain_path($this->package_dir);

        $source = __DIR__.'/../../../stubs/gitlab/'.$file;

        $target = $packagePath.'/'.$file;

        if ($this->option('force') || file_exists($target) === false) {
            File::copy($source, $target);

            $this->info('Gitlab file created successfully.');

            return self::SUCCESS;
        }

        $this->warn('Gitlab file already exists!');

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
