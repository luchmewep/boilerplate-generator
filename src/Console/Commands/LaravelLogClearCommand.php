<?php

namespace Luchavez\BoilerplateGenerator\Console\Commands;

use Illuminate\Console\Command;
use Luchavez\StarterKit\Traits\UsesCommandCustomMessagesTrait;

/**
 * Class LaravelLogClearCommand
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 */
class LaravelLogClearCommand extends Command
{
    use UsesCommandCustomMessagesTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'bg:log:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear laravel.log contents.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $log_path = storage_path('logs/laravel.log');

        $result = self::FAILURE;

        if (file_exists($log_path)) {
            file_put_contents($log_path, '');

            $result = self::SUCCESS;
        }

        if ($result == self::SUCCESS) {
            $this->done('Successfully cleared log contents.');
        } else {
            $this->failed('Failed to clear log contents.');
        }

        return $result;
    }
}
