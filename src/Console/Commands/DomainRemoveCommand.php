<?php

namespace Luchavez\BoilerplateGenerator\Console\Commands;

use Illuminate\Support\Facades\File;
use function Laravel\Prompts\text;
use Luchavez\BoilerplateGenerator\Exceptions\MissingNameArgumentException;
use Luchavez\BoilerplateGenerator\Exceptions\PackageNotFoundException;

/**
 * Class DomainRemoveCommand
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 */
class DomainRemoveCommand extends DomainEnableCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'bg:domain:remove';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove a domain or module in Laravel or in a specific package.';

    /**
     * Execute the console command.
     *
     * @throws MissingNameArgumentException
     * @throws PackageNotFoundException
     */
    public function handle(): bool
    {
        $this->setVendorPackageDomain(show_domain_choices: false);

        $this->domain_name = $this->getNameInput();

        // Get domain information from the list
        $domain = boilerplateGenerator()
            ->getSummarizedDomains(package: $this->package_dir, with_providers: true)
            ->get($this->domain_name);

        // Fail if not found
        if (! $domain) {
            $this->failed('Domain not found: '.$this->domain_name);

            return self::FAILURE;
        }

        // Disable if still enabled
        if ($domain['is_enabled']) {
            $disable_args['--no-interaction'] = true;

            if ($this->package_dir) {
                $disable_args['--package'] = $this->package_dir;
            }

            $disable_args['name'] = $this->domain_name;
            $this->call('bg:domain:disable', $disable_args);
        } else {
            $this->note('Domain is already disabled.');
        }

        // Get the subdomains from the list
        $sub_domains = boilerplateGenerator()
            ->getSubDomains($this->domain_name, $this->package_dir);

        // Make a warning regarding the subdomains
        if ($sub_domains->count()) {
            $list = $sub_domains->keys()->map(fn ($key) => $this->getBoldText($key))->join(',', ', and ');
            $this->warning('Found one or more child domains that will be deleted: '.$list);
        }

        // Ask for confirmation before deletion
        $confirm_name = text(
            label: 'Enter domain name to confirm deletion',
            placeholder: $this->domain_name,
            default: $this->domain_name,
            required: true
        );

        if ($confirm_name != $this->domain_name) {
            $this->failed('Domain name is incorrect. Cancelled domain deletion.');

            return self::FAILURE;
        }

        // Proceed with domain deletion
        if (File::deleteDirectory($domain['path'])) {
            $this->done('Successfully deleted the domain: '.$this->getBoldText($this->domain_name));

            return self::SUCCESS;
        }

        $this->failed('Failed to delete the domain: '.$this->getBoldText($this->domain_name));

        return self::FAILURE;
    }
}
