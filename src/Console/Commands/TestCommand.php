<?php

namespace Luchavez\BoilerplateGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Luchavez\BoilerplateGenerator\Traits\UsesCommandMultipleTargetsTrait;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;

/**
 * Class TestCommand
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 *
 * @since  2021-11-17
 */
class TestCommand extends Command
{
    use UsesCommandMultipleTargetsTrait;

    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'bg:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the application and package tests.';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        // To ignore validation errors
        $this->ignoreValidationErrors();

        $this->addMultipleTargetsOption();
    }

    /**
     * @return int
     */
    public function handle(): int
    {
        $this->setTargetsAndDomains();

        // default behavior
        $test_packages = false;

        // needed checks related to default package
        $has_root = $this->hasRoot(); // check again
        $has_other_than_root = $this->hasOtherThanRoot();

        // if packages is null or has root already
        $test_root = (! $this->targets || $has_root) && ! $this->isPackagesOnly();

        // Decide whether to test packages
        if ($this->isRootAndPackages() || $this->isPackagesOnly() || $has_other_than_root) {
            $test_packages = true;
        }

        // Actual Test Executions
        $test_paths = [];
        $dot_notation = 'directories.tests.path';

        $add_domains_to_test_paths = function (array $domains, string $package = null) use ($dot_notation, &$test_paths) {
            collect($domains)
                ->when($this->domain_search, fn ($collection) => $collection->filter(fn ($details, $domain) => $domain == $this->domain_search || Str::contains($domain, $this->domain_search)))
                ->each(function ($details, $domain) use ($package, $dot_notation, &$test_paths) {
                    $test_paths[] = [
                        'package' => $package,
                        'domain' => $domain,
                        'tests_path' => Arr::get($details, $dot_notation),
                    ];
                });
        };

        // Get root and its domains tests paths
        if ($test_root) {
            if (! $this->domain_search) {
                $test_paths[] = [
                    'package' => null,
                    'domain' => null,
                    'tests_path' => null,
                ];
            }
            $domains = starterKit()->getRoot()->get('domains', []);
            $add_domains_to_test_paths($domains);
        }

        // Get packages and each package's domains tests paths
        if ($test_packages) {
            boilerplateGenerator()->getSummarizedPackages(is_loaded: true, with_details: true)
                ->when($has_other_than_root, fn (Collection $collection) => $collection->only($this->targets))
                ->each(function (array $details, string $package) use ($add_domains_to_test_paths, $dot_notation, &$test_paths) {
                    if (! $this->domain_search) {
                        $test_paths[] = [
                            'package' => $package,
                            'tests_path' => Arr::get($details, $dot_notation),
                        ];
                    }
                    $domains = Arr::get($details, 'domains', []);
                    $add_domains_to_test_paths($domains, $package);
                });
        }

        if ($count = count($test_paths)) {
            $progress = $this->output->createProgressBar($count);

            // function to run the tests
            foreach ($test_paths as $test_path) {
                $progress->advance();
                $progress->display();
                $this->executeTests(...$test_path);
            }

            $progress->finish();

            return self::SUCCESS;
        }

        $this->failed('No tests found.');

        return self::FAILURE;
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
     * @param  string|null  $package
     * @param  string|null  $domain
     * @param  string|null  $tests_path
     * @return void
     */
    public function executeTests(string $package = null, string $domain = null, string $tests_path = null): void
    {
        $get_blinking_icon = function (string $str) {
            return $this->areIconsBlinking() ? '<blink>'.$str.'</blink>' : $str;
        };

        $message = $get_blinking_icon('🧪').' Running tests for ';
        if ($package && $domain) {
            $message .= $this->getBoldText($domain).' domain of '.$this->getBoldText($package).' 📦';
        } elseif ($package) {
            $message .= $this->getBoldText($package).' 📦';
        } elseif ($domain) {
            $message .= $this->getBoldText($domain).' domain of '.$this->getBoldText('Laravel');
        } else {
            $message .= $this->getBoldText('Laravel');
        }

        $this->newLine(2);
        $this->ongoing($message);

        if (($package || $domain) && ! $tests_path) {
            $this->warning($get_blinking_icon('🔍').' Tests folder is not found.');
            $this->newLine();

            return;
        }

        $test_directory = null;

        if ($tests_path) {
            $tests_path = Str::of($tests_path)
                ->after(base_path())
                ->replace('\\', '/')
                ->ltrim('/')
                ->finish('/tests')
                ->jsonSerialize();

            $test_directory = '--test-directory='.$tests_path;
        }

        $command = collect(['php', 'artisan', 'test', $tests_path, $test_directory])->merge($this->collectRawOptions())->filter();

        $this->ongoing($get_blinking_icon('🏃').' Running command: '.$this->getBoldText($command->implode(' ')), false);

        $process = new Process($command->toArray());
        $process->setTty(true);
        $process->run();
    }

    /**
     * @return Collection
     */
    protected function collectRawOptions(): Collection
    {
        $argv = collect($_SERVER['argv']);

        // Only get the args that pass
        return $argv->filter(function ($arg) {
            $arg = Str::of($arg)->before('=');

            return $arg->startsWith('--') && ! $arg->contains(['all', 'packages', 'package', 'test-directory', 'domain', 'blink']);
        });
    }

    /**
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            new InputOption('blink', null, InputOption::VALUE_NONE, 'Enable blinking icons.'),
        ];
    }

    /**
     * @return string|null
     */
    public function areIconsBlinking(): ?string
    {
        return $this->option('blink');
    }
}
