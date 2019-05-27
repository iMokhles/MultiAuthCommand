<?php

namespace iMokhles\MultiAuthCommand;

use Illuminate\Support\ServiceProvider;

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
            return $app['iMokhles\MultiAuthCommand\Command\MultiAuthPrepare'];
        });
        $this->app->singleton('command.imokhles.make.multi-auth.list-themes', function ($app) {
            return $app['iMokhles\MultiAuthCommand\Command\MultiAuthListThemes'];
        });
        $this->commands([
            'MultiAuthPrepare' => 'command.imokhles.make.multi-auth',
            'MultiAuthListThemes' => 'command.imokhles.make.multi-auth.list-themes',
        ]);
    }
}

