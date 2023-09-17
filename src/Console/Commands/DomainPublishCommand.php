<?php

namespace Luchavez\BoilerplateGenerator\Console\Commands;

use Illuminate\Console\Command;
use Luchavez\BoilerplateGenerator\Exceptions\MissingNameArgumentException;
use Luchavez\BoilerplateGenerator\Exceptions\PackageNotFoundException;
use Luchavez\BoilerplateGenerator\Traits\UsesCommandVendorPackageDomainTrait;

/**
 * Class DomainPublishCommand
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 */
class DomainPublishCommand extends Command
{
    use UsesCommandVendorPackageDomainTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'bg:domain:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish a domain or module in Laravel or in a specific package.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->addPackageDomainOptions();
    }

    /**
     * @return int
     *
     * @throws MissingNameArgumentException|PackageNotFoundException
     */
    public function handle(): int
    {
        $this->setVendorPackageDomain(true, false);

        $domains = starterKit()->getDomains($this->package_dir);

        $this->table(
            ['Domain', 'Path'],
            $domains?->mapWithKeys(fn ($item, $key) => [[$key, $item]]) ?? []
        );

        return self::SUCCESS;
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
