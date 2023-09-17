<?php

namespace Luchavez\BoilerplateGenerator\Console\Commands;

use Illuminate\Console\Command;
use Luchavez\BoilerplateGenerator\Traits\UsesCommandFilterTrait;
use Luchavez\StarterKit\Traits\UsesCommandCustomMessagesTrait;

/**
 * Class PackageListCommand
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 *
 * @since  2021-12-06
 */
class PackageListCommand extends Command
{
    use UsesCommandFilterTrait;
    use UsesCommandCustomMessagesTrait;

    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'bg:package:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all locally installed packages.';

    public function __construct()
    {
        parent::__construct();

        $this->addFilterOptions('package');
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // Set Symfony Console Formatter
        $this->setupOutputFormatters();

        $rows = $this->getPackagesRows();

        if (count($rows)) {
            $this->createTable(
                'Packages',
                ['Package', 'Path', 'Is Local?', 'Is Enabled?', 'Is Loaded?'],
                $rows
            )?->render();
        } else {
            $this->failed('No packages found.');
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
    public function getPackagesRows(): array
    {
        $yes = $this->createTableCell('YES');
        $no = $this->createTableCell('NO', 'red-bold');

        // Handle is_local, is_enabled, is_loaded, and filter
        $is_local = $this->validateBoolean('local');
        $is_enabled = $this->validateBoolean('enabled');
        $is_loaded = $this->validateBoolean('loaded');
        $filter = $this->option('filter');

        return boilerplateGenerator()
            ->getSummarizedPackages($filter, $is_local, $is_enabled, $is_loaded)
            ->map(function (array $arr, string $package) use ($yes, $no) {
                return [
                    $package,
                    $arr['path'],
                    $arr['is_local'] ? $yes : $no,
                    $arr['is_enabled'] ? $yes : $no,
                    $arr['is_loaded'] ? $yes : $no,
                ];
            })
            ->toArray();
    }
}
