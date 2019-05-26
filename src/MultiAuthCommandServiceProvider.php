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
        $this->commands('command.imokhles.make.multi-auth');
    }
}

