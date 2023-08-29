<?php

namespace Luchavez\BoilerplateGenerator\Providers;

use Luchavez\BoilerplateGenerator\Console\Commands\ClassMakeCommand;
use Luchavez\BoilerplateGenerator\Console\Commands\ConfigMakeCommand;
use Luchavez\BoilerplateGenerator\Console\Commands\DataFactoryMakeCommand;
use Luchavez\BoilerplateGenerator\Console\Commands\DataMakeCommand;
use Luchavez\BoilerplateGenerator\Console\Commands\DescribeCommand;
use Luchavez\BoilerplateGenerator\Console\Commands\DocsGenCommand;
use Luchavez\BoilerplateGenerator\Console\Commands\DomainCreateCommand;
use Luchavez\BoilerplateGenerator\Console\Commands\DomainDisableCommand;
use Luchavez\BoilerplateGenerator\Console\Commands\DomainEnableCommand;
use Luchavez\BoilerplateGenerator\Console\Commands\DomainListCommand;
use Luchavez\BoilerplateGenerator\Console\Commands\DomainRemoveCommand;
use Luchavez\BoilerplateGenerator\Console\Commands\DtoMakeCommand;
use Luchavez\BoilerplateGenerator\Console\Commands\EnvPublishCommand;
use Luchavez\BoilerplateGenerator\Console\Commands\ExtendedMakeCast;
use Luchavez\BoilerplateGenerator\Console\Commands\ExtendedMakeChannel;
use Luchavez\BoilerplateGenerator\Console\Commands\ExtendedMakeCommand;
use Luchavez\BoilerplateGenerator\Console\Commands\ExtendedMakeComponent;
use Luchavez\BoilerplateGenerator\Console\Commands\ExtendedMakeController;
use Luchavez\BoilerplateGenerator\Console\Commands\ExtendedMakeEvent;
use Luchavez\BoilerplateGenerator\Console\Commands\ExtendedMakeException;
use Luchavez\BoilerplateGenerator\Console\Commands\ExtendedMakeFactory;
use Luchavez\BoilerplateGenerator\Console\Commands\ExtendedMakeJob;
use Luchavez\BoilerplateGenerator\Console\Commands\ExtendedMakeListener;
use Luchavez\BoilerplateGenerator\Console\Commands\ExtendedMakeMail;
use Luchavez\BoilerplateGenerator\Console\Commands\ExtendedMakeMiddleware;
use Luchavez\BoilerplateGenerator\Console\Commands\ExtendedMakeMigration;
use Luchavez\BoilerplateGenerator\Console\Commands\ExtendedMakeModel;
use Luchavez\BoilerplateGenerator\Console\Commands\ExtendedMakeNotification;
use Luchavez\BoilerplateGenerator\Console\Commands\ExtendedMakeObserver;
use Luchavez\BoilerplateGenerator\Console\Commands\ExtendedMakePolicy;
use Luchavez\BoilerplateGenerator\Console\Commands\ExtendedMakeProvider;
use Luchavez\BoilerplateGenerator\Console\Commands\ExtendedMakeRequest;
use Luchavez\BoilerplateGenerator\Console\Commands\ExtendedMakeResource;
use Luchavez\BoilerplateGenerator\Console\Commands\ExtendedMakeRule;
use Luchavez\BoilerplateGenerator\Console\Commands\ExtendedMakeSeeder;
use Luchavez\BoilerplateGenerator\Console\Commands\ExtendedMakeTest;
use Luchavez\BoilerplateGenerator\Console\Commands\FacadeMakeCommand;
use Luchavez\BoilerplateGenerator\Console\Commands\GitlabCIMakeCommand;
use Luchavez\BoilerplateGenerator\Console\Commands\HelperMakeCommand;
use Luchavez\BoilerplateGenerator\Console\Commands\InstallCommand;
use Luchavez\BoilerplateGenerator\Console\Commands\InterfaceMakeCommand;
use Luchavez\BoilerplateGenerator\Console\Commands\LaravelLogClearCommand;
use Luchavez\BoilerplateGenerator\Console\Commands\PackageCloneCommand;
use Luchavez\BoilerplateGenerator\Console\Commands\PackageCreateCommand;
use Luchavez\BoilerplateGenerator\Console\Commands\PackageDisableCommand;
use Luchavez\BoilerplateGenerator\Console\Commands\PackageEnableCommand;
use Luchavez\BoilerplateGenerator\Console\Commands\PackageListCommand;
use Luchavez\BoilerplateGenerator\Console\Commands\PackagePublishCommand;
use Luchavez\BoilerplateGenerator\Console\Commands\PackageRemoveCommand;
use Luchavez\BoilerplateGenerator\Console\Commands\PestInstallCommand;
use Luchavez\BoilerplateGenerator\Console\Commands\RepositoryMakeCommand;
use Luchavez\BoilerplateGenerator\Console\Commands\RouteMakeCommand;
use Luchavez\BoilerplateGenerator\Console\Commands\ScopeMakeCommand;
use Luchavez\BoilerplateGenerator\Console\Commands\ServiceMakeCommand;
use Luchavez\BoilerplateGenerator\Console\Commands\TestCommand;
use Luchavez\BoilerplateGenerator\Console\Commands\TraitMakeCommand;
use Luchavez\BoilerplateGenerator\Services\BoilerplateGenerator;
use Luchavez\StarterKit\Abstracts\BaseStarterKitServiceProvider as ServiceProvider;

/**
 * Class BoilerplateGeneratorServiceProvider
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 *
 * @since 2022-10-10
 */
class BoilerplateGeneratorServiceProvider extends ServiceProvider
{
    protected array $commands = [
        // Extended
        ExtendedMakeCast::class,
        ExtendedMakeChannel::class,
        ExtendedMakeCommand::class,
        ExtendedMakeComponent::class,
        ExtendedMakeController::class,
        ExtendedMakeEvent::class,
        ExtendedMakeException::class,
        ExtendedMakeFactory::class,
        ExtendedMakeJob::class,
        ExtendedMakeListener::class,
        ExtendedMakeMail::class,
        ExtendedMakeMiddleware::class,
        ExtendedMakeMigration::class,
        ExtendedMakeModel::class,
        ExtendedMakeNotification::class,
        ExtendedMakeObserver::class,
        ExtendedMakePolicy::class,
        ExtendedMakeProvider::class,
        ExtendedMakeRequest::class,
        ExtendedMakeResource::class,
        ExtendedMakeRule::class,
        ExtendedMakeSeeder::class,
        ExtendedMakeTest::class,

        // Additional
        ClassMakeCommand::class,
        ConfigMakeCommand::class,
        DataMakeCommand::class,
        DataFactoryMakeCommand::class,
        DescribeCommand::class,
        DocsGenCommand::class,
        DtoMakeCommand::class,
        FacadeMakeCommand::class,
        GitlabCIMakeCommand::class,
        HelperMakeCommand::class,
        InterfaceMakeCommand::class,
        RepositoryMakeCommand::class,
        RouteMakeCommand::class,
        ScopeMakeCommand::class,
        ServiceMakeCommand::class,
        TestCommand::class,
        TraitMakeCommand::class,
        InstallCommand::class,
        EnvPublishCommand::class,
        LaravelLogClearCommand::class,
        PestInstallCommand::class,

        // Packages
        PackageCreateCommand::class,
        PackageRemoveCommand::class,
        PackageCloneCommand::class,
        PackageEnableCommand::class,
        PackageDisableCommand::class,
        PackagePublishCommand::class,
        PackageListCommand::class,

        // Domains
        DomainCreateCommand::class,
        DomainEnableCommand::class,
        DomainDisableCommand::class,
        DomainRemoveCommand::class,
        DomainListCommand::class,
        // Todo: DomainPublishCommand::class,
    ];

    /**
     * Publishable Environment Variables
     *
     * @example [ 'HELLO_WORLD' => true ]
     *
     * @var array
     */
    protected array $env_vars = [
        'BG_PEST_ENABLED' => true,
        'BG_AUTHOR_NAME' => 'James Carlo Luchavez',
        'BG_AUTHOR_EMAIL' => 'jamescarloluchavez@gmail.com',
        'BG_AUTHOR_HOMEPAGE' => 'https://www.linkedin.com/in/jsluchavez',
        'BG_PACKAGE_SKELETON' => 'https://github.com/luchavez-technologies/laravel-package-skeleton/archive/develop.zip',
    ];

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        // Register the service the package provides.
        $this->app->singleton('boilerplate-generator', fn () => new BoilerplateGenerator());
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes(
            [
                __DIR__.'/../../config/boilerplate-generator.php' => config_path('boilerplate-generator.php'),
            ],
            'boilerplate-generator.config'
        );

        // Registering package commands.
        $this->commands($this->commands);
    }
}
