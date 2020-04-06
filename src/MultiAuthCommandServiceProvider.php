<?php

namespace iMokhles\MultiAuthCommand;

use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Support\Composer;
use Illuminate\Support\ServiceProvider;
use iMokhles\MultiAuthCommand\Command\MultiAuthListThemes;
use iMokhles\MultiAuthCommand\Command\MultiAuthPrepare;

class MultiAuthCommandServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->registerInstallCommand();
    }

    /**
     * Register the make:multi-auth command.
     */
    private function registerInstallCommand()
    {
        $this->app->singleton('command.imokhles.make.multi-auth', function ($app) {
            return new MultiAuthPrepare(new MigrationCreator($app['files'], $app->basePath('Stubs')), new Composer($app['files']));
        });
        $this->app->singleton('command.imokhles.make.multi-auth.list-themes', function ($app) {
            return new MultiAuthListThemes();
        });
        $this->commands([
            'MultiAuthPrepare' => 'command.imokhles.make.multi-auth',
            'MultiAuthListThemes' => 'command.imokhles.make.multi-auth.list-themes',
        ]);
    }
}

