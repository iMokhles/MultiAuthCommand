<?php
/**
 * Created by PhpStorm.
 * User: imokhles
 * Date: 2019-05-27
 * Time: 12:37
 */

namespace iMokhles\MultiAuthCommand\Command;

use Illuminate\Database\Console\Migrations\BaseCommand;

class MultiAuthListThemes extends BaseCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:multi_auth:list_themes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'list MultiAuthCommand supported themes';

    /**
     * @var
     */
    protected $progressBar;

    /**
     * Execute the console command.
     *
     * @return boolean
     */
    public function handle()
    {
        $themes = $this->listSupportedThemes();

        $this->progressBar = $this->output->createProgressBar(count($themes));
        $this->progressBar->start();

        $this->line(" Listing supported themes. Please wait...");
        foreach ($themes as $theme => $link) {
            $this->line(" THEME_NAME: $theme\nTHEME_LINK: $link\n");
            $this->progressBar->advance();
        }

        $this->progressBar->finish();

        return true;
    }

    /**
     * @return array
     */
    private function listSupportedThemes() {

        return [
            'adminlte2' => 'https://adminlte.io/themes/AdminLTE/index2.html',
            'highadmin' => 'https://themeforest.net/item/highdmin-responsive-bootstrap-4-admin-dashboard/21233941',
            'startui' => 'https://themeforest.net/item/startui-premium-bootstrap-4-admin-dashboard-template/15228250',
        ];
    }
}