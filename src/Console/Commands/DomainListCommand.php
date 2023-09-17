<?php

namespace Luchavez\BoilerplateGenerator\Console\Commands;

use Illuminate\Console\Command;
use Luchavez\BoilerplateGenerator\Exceptions\MissingNameArgumentException;
use Luchavez\BoilerplateGenerator\Exceptions\PackageNotFoundException;
use Luchavez\BoilerplateGenerator\Traits\UsesCommandFilterTrait;
use Luchavez\BoilerplateGenerator\Traits\UsesCommandVendorPackageDomainTrait;

/**
 * Class DomainListCommand
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 */
class DomainListCommand extends Command
{
    use UsesCommandVendorPackageDomainTrait;
    use UsesCommandFilterTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'bg:domain:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all domains or modules within Laravel or within a specific package.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->addPackageDomainOptions(has_domain_choices: false);

        $this->addFilterOptions('domain');
    }

    /**
     * @return int
     *
     * @throws MissingNameArgumentException|PackageNotFoundException
     */
    public function handle(): int
    {
        // Set Symfony Console Formatter
        $this->setupOutputFormatters();

        $this->setVendorPackageDomain(true, false);

        $rows = $this->getDomainRows();

        if (count($rows)) {
            $this->createTable(
                'Domains',
                ['Domain', 'Path', 'Is Local?', 'Is Enabled?', 'Is Loaded?'],
                $rows
            )?->render();

            return self::SUCCESS;
        } else {
            $this->failed('No domains found.');

            return self::FAILURE;
        }
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
     * @return array
     */
    public function getDomainRows(): array
    {
        $yes = $this->createTableCell('YES');
        $no = $this->createTableCell('NO', 'red-bold');

        // Handle is_local, is_enabled, is_loaded, and filter
        $is_local = $this->validateBoolean('local');
        $is_enabled = $this->validateBoolean('enabled');
        $is_loaded = $this->validateBoolean('loaded');
        $filter = $this->option('filter');

        return boilerplateGenerator()
            ->getSummarizedDomains($this->package_dir, $filter, $is_local, $is_enabled, $is_loaded)
            ->map(function (array $arr, string $package) use ($yes, $no) {
                return [
                    $package,
                    $arr['path'],
                    $arr['is_local'] ? $yes : $no,
                    $arr['is_enabled'] ? $yes : $no,
                    $arr['is_loaded'] ? $yes : $no,
                ];
            })
            ->sortKeys()
            ->toArray();
    }
}
