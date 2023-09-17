<?php

namespace Luchavez\BoilerplateGenerator\Traits;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

/**
 * Trait UsesCommandFilterTrait
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 */
trait UsesCommandFilterTrait
{
    /**
     * @param  string  $type
     * @return void
     */
    protected function addFilterOptions(string $type): void
    {
        $types = Str::plural($type);

        $this->getDefinition()->addOptions(
            [
                new InputOption(
                    'local',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'Whether or not to show local '.$types.'.'
                ),
                new InputOption(
                    'enabled',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'Whether or not to show enabled '.$types.'.'
                ),
                new InputOption(
                    'loaded',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'Whether or not to show loaded '.$types.'.'
                ),
                new InputOption(
                    'filter',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'Filter '.$type.' by name.'
                ),
            ]
        );
    }

    /**
     * @param  string  $key
     * @return bool|null
     */
    protected function validateBoolean(string $key): ?bool
    {
        if ($option = $this->option($key)) {
            return filter_var($option, FILTER_VALIDATE_BOOLEAN);
        }

        return null;
    }
}
