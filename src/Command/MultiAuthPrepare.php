<?php

namespace iMokhles\MultiAuthCommand\Command;

use Illuminate\Console\Command;
use Illuminate\Database\Console\Migrations\BaseCommand;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Support\Composer;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class MultiAuthPrepare extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:multi_auth {name} {--admin_theme= : chose the theme you want}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create multi authentication guards with dashboard panel';

    /**
     * @var
     */
    protected $progressBar;

    /**
     * The migration creator instance.
     *
     * @var \Illuminate\Database\Migrations\MigrationCreator
     */
    protected $creator;

    /**
     * The Composer instance.
     *
     * @var \Illuminate\Support\Composer
     */
    protected $composer;

    /**
     * Create a new migration install command instance.
     *
     * @param  \Illuminate\Database\Migrations\MigrationCreator  $creator
     * @param  \Illuminate\Support\Composer  $composer
     */
    public function __construct(MigrationCreator $creator, Composer $composer)
    {
        parent::__construct();
        $this->creator = $creator;
        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @return boolean
     */
    public function handle()
    {
        $this->progressBar = $this->output->createProgressBar(15);
        $this->progressBar->start();


        $this->line(" Preparing For MultiAuth. Please wait...");
        $this->progressBar->advance();

        $name = ucfirst($this->getParsedNameInput());

        $admin_theme = $this->option('admin_theme');
        if (is_null($admin_theme)) {
            $admin_theme = 'adminlte2';
        }



        if ($this->checkIfThemeFileExistsOrNot($admin_theme)) {
            $this->line(" installing migrations...");
            $this->installMigration();
            $this->progressBar->advance();

            $this->line(" installing notifications database table...");
            $this->installNotificationsDatabase();
            $this->progressBar->advance();

            $this->line(" installing models...");
            $this->installModel();
            $this->progressBar->advance();

            $this->line(" installing route maps...");
            $this->installRouteMaps();
            $this->progressBar->advance();

            $this->line(" installing route files...");
            $this->installRouteFiles();
            $this->progressBar->advance();

            $this->line(" installing controllers...");
            $this->installControllers();
            $this->progressBar->advance();

            $this->line(" installing requests...");
            $this->installRequests();
            $this->progressBar->advance();

            $this->line(" installing configs...");
            $this->installConfigs();
            $this->progressBar->advance();

            $this->line(" installing middleware...");
            $this->installMiddleware();
            $this->progressBar->advance();

            $this->line(" installing unauthenticated function...");
            $this->installUnauthenticated();
            $this->progressBar->advance();

            $this->line(" installing views...");
            $this->installView($admin_theme);
            $this->progressBar->advance();

            $this->line(" installing languages files...");
            $this->installLangs();
            $this->progressBar->advance();

            $this->line(" installing project config file...");
            $this->installProjectConfig($admin_theme);
            $this->progressBar->advance();

            if (array_key_exists($admin_theme, MultiAuthListThemes::listFreeThemes())) {
                $this->line(" installing admin panel files under public folder...");
                $this->installPublicFilesIfNeeded($admin_theme);
                $this->progressBar->advance();
            }


            $this->line(" installing prologue alert...");
            $this->installPrologueAlert();
            $this->progressBar->advance();

            $this->line(" dump autoload...");
            $this->composer->dumpAutoloads();
            $this->progressBar->advance();

            $this->progressBar->finish();
            $this->info(" finished ".$name." setup with Backpack panel.");
        } else {
            $this->progressBar->advance();
            $this->progressBar->finish();
            $this->line(" failed: ".$admin_theme." theme not found");
        }
        return true;
    }


    /**
     * @param $admin_theme
     * @return bool
     */
    private function checkIfThemeFileExistsOrNot($admin_theme) {
        return (file_exists(__DIR__ . '/../Stubs/Views/'.$admin_theme.'/dashboard.blade.stub'));
    }

    /**
     * @return bool
     */
    public function isAlreadySetup() {
        $name = ucfirst($this->getParsedNameInput());

        $routeServicesContent = file_get_contents($this->getRouteServicesPath());

        if (str_contains($routeServicesContent,'$this->map'.$name.'Routes();')) {
            return true;
        }
        return false;
    }

    /**
     * Install Migration.
     *
     * @return boolean
     */
    public function installMigration()
    {
        $nameSmallPlural = str_plural(snake_case($this->getParsedNameInput()));
        $name = ucfirst($this->getParsedNameInput());
        $namePlural = str_plural($name);



        $modelTableContent = file_get_contents(__DIR__ . '/../Stubs/Migration/modelTable.stub');
        $modelTableContentNew = str_replace([
            '{{$namePlural}}',
            '{{$nameSmallPlural}}',
        ], [
            $namePlural,
            $nameSmallPlural
        ], $modelTableContent);


        $modelResetPasswordTableContent = file_get_contents(__DIR__ . '/../Stubs/Migration/passwordResetsTable.stub');
        $modelResetPasswordTableContentNew = str_replace([
            '{{$namePlural}}',
            '{{$nameSmallPlural}}',
        ], [
            $namePlural,
            $nameSmallPlural
        ], $modelResetPasswordTableContent);


        $migrationName = date('Y_m_d_His') . '_'.'create_' . str_plural(snake_case($name)) .'_table.php';
        $migrationModelPath = $this->getMigrationPath().DIRECTORY_SEPARATOR.$migrationName;
        file_put_contents($migrationModelPath, $modelTableContentNew);

        $migrationResetName = date('Y_m_d_His') . '_'
            .'create_' . str_plural(snake_case($name))
            .'_password_resets_table.php';
        $migrationResetModelPath = $this->getMigrationPath().DIRECTORY_SEPARATOR.$migrationResetName;
        file_put_contents($migrationResetModelPath, $modelResetPasswordTableContentNew);

        return true;

    }

    /**
     * Install Model.
     *
     * @return boolean
     */
    public function installModel()
    {
        $nameSmall = snake_case($this->getParsedNameInput());
        $name = ucfirst($this->getParsedNameInput());


        $arrayToChange = [
            '{{$name}}',
        ];

        $newChanges = [
            $name,
        ];
        $nameSmallPlural = str_plural(snake_case($this->getParsedNameInput()));
        array_push($arrayToChange, '{{$nameSmallPlural}}');
        array_push($arrayToChange, '{{$nameSmall}}');
        array_push($newChanges, $nameSmallPlural);
        array_push($newChanges, $nameSmall);

        $modelContent = file_get_contents(__DIR__ . '/../Stubs/Model/model.stub');
        $modelContentNew = str_replace($arrayToChange, $newChanges, $modelContent);

        $createFolder = $this->getAppFolderPath().DIRECTORY_SEPARATOR."Models";
        if (!file_exists($createFolder)) {
            mkdir($createFolder);
        }

        $modelPath = $createFolder.DIRECTORY_SEPARATOR.$name.".php";
        file_put_contents($modelPath, $modelContentNew);



        $resetNotificationContent = file_get_contents(__DIR__ . '/../Stubs/Notification/resetPasswordNotification.stub');
        $resetNotificationContentNew = str_replace([
            '{{$name}}',
            '{{$nameSmall}}',
        ], [
            $name,
            $nameSmall
        ], $resetNotificationContent);

        $verifyEmailContent = file_get_contents(__DIR__ . '/../Stubs/Notification/verifyEmailNotification.stub');
        $verifyEmailContentNew = str_replace([
            '{{$name}}',
            '{{$nameSmall}}',
        ], [
            $name,
            $nameSmall
        ], $verifyEmailContent);

        $createFolder = $this->getAppFolderPath().DIRECTORY_SEPARATOR."Notifications";
        if (!file_exists($createFolder)) {
            mkdir($createFolder);
        }

        $resetNotificationPath = $createFolder.DIRECTORY_SEPARATOR.$name."ResetPasswordNotification.php";
        file_put_contents($resetNotificationPath, $resetNotificationContentNew);

        $verifyEmailPath = $createFolder.DIRECTORY_SEPARATOR.$name."VerifyEmailNotification.php";
        file_put_contents($verifyEmailPath, $verifyEmailContentNew);

        return true;

    }

    /**
     * Install RouteMaps.
     *
     * @return boolean
     */

    public function installRouteMaps()
    {
        $nameSmall = snake_case($this->getParsedNameInput());
        $name = ucfirst($this->getParsedNameInput());
        $mapCallFunction = file_get_contents(__DIR__ . '/../Stubs/Route/mapRoute.stub');
        $mapCallFunctionNew = str_replace('{{$name}}', "$name", $mapCallFunction);
        $this->insert($this->getRouteServicesPath(), '$this->mapWebRoutes();', $mapCallFunctionNew, true);
        $mapFunction = file_get_contents(__DIR__ . '/../Stubs/Route/mapRouteFunction.stub');
        $mapFunctionNew = str_replace([
            '{{$name}}',
            '{{$nameSmall}}'
        ], [
            "$name",
            "$nameSmall"
        ], $mapFunction);
        $this->insert($this->getRouteServicesPath(), '        //
    }', $mapFunctionNew, true);
        return true;
    }

    /**
     * Install RouteFile.
     *
     * @return boolean
     */

    public function installRouteFiles()
    {
        $nameSmall = snake_case($this->getParsedNameInput());
        $name = ucfirst($this->getParsedNameInput());
        $createFolder = $this->getRoutesFolderPath().DIRECTORY_SEPARATOR.$nameSmall;
        if (!file_exists($createFolder)) {
            mkdir($createFolder);
        }
        $routeFileContent = file_get_contents(__DIR__ . '/../Stubs/Route/routeFile.stub');
        $routeFileContentNew = str_replace([
            '{{$name}}',
            '{{$nameSmall}}'
        ], [
            "$name",
            "$nameSmall"
        ], $routeFileContent);
        $routeFile = $createFolder.DIRECTORY_SEPARATOR.$nameSmall.".php";
        file_put_contents($routeFile, $routeFileContentNew);
        return true;
    }

    /**
     * Install Controller.
     *
     * @return boolean
     */

    public function installControllers()
    {
        $nameSmall = snake_case($this->getParsedNameInput());
        $nameSmallPlural = str_plural(snake_case($this->getParsedNameInput()));
        $name = ucfirst($this->getParsedNameInput());

        $nameFolder = $this->getControllersPath().DIRECTORY_SEPARATOR.$name;
        if (!file_exists($nameFolder)) {
            mkdir($nameFolder);
        }

        $authFolder = $nameFolder.DIRECTORY_SEPARATOR."Auth";
        if (!file_exists($authFolder)) {
            mkdir($authFolder);
        }

        $controllerContent = file_get_contents(__DIR__ . '/../Stubs/Controllers/Controller.stub');
        $homeControllerContent = file_get_contents(__DIR__ . '/../Stubs/Controllers/DashboardController.stub');
        $loginControllerContent = file_get_contents(__DIR__ . '/../Stubs/Controllers/Auth/LoginController.stub');
        $forgotControllerContent = file_get_contents(__DIR__ . '/../Stubs/Controllers/Auth/ForgotPasswordController.stub');
        $registerControllerContent = file_get_contents(__DIR__ . '/../Stubs/Controllers/Auth/RegisterController.stub');
        $resetControllerContent = file_get_contents(__DIR__ . '/../Stubs/Controllers/Auth/ResetPasswordController.stub');
        $myAccountControllerContent = file_get_contents(__DIR__ . '/../Stubs/Controllers/Auth/MyAccountController.stub');
        $verificationControllerContent = file_get_contents(__DIR__ . '/../Stubs/Controllers/Auth/VerificationController.stub');

        $controllerFileContentNew = str_replace('{{$name}}', "$name", $controllerContent);

        $homeFileContentNew = str_replace([
            '{{$name}}',
            '{{$nameSmall}}'
        ], [
            "$name",
            "$nameSmall"
        ], $homeControllerContent);

        $loginFileContentNew = str_replace([
            '{{$name}}',
            '{{$nameSmall}}'
        ], [
            "$name",
            "$nameSmall"
        ], $loginControllerContent);

        $forgotFileContentNew = str_replace([
            '{{$name}}',
            '{{$nameSmall}}',
            '{{$nameSmallPlural}}'
        ], [
            "$name",
            "$nameSmall",
            "$nameSmallPlural"
        ], $forgotControllerContent);

        $registerFileContentNew = str_replace([
            '{{$name}}',
            '{{$nameSmall}}',
            '{{$nameSmallPlural}}'
        ], [
            "$name",
            "$nameSmall",
            "$nameSmallPlural"
        ], $registerControllerContent);

        $resetFileContentNew = str_replace([
            '{{$name}}',
            '{{$nameSmall}}',
            '{{$nameSmallPlural}}'
        ], [
            "$name",
            "$nameSmall",
            "$nameSmallPlural"
        ], $resetControllerContent);

        $myAccountFileContentNew = str_replace([
            '{{$name}}',
            '{{$nameSmall}}'
        ], [
            "$name",
            "$nameSmall"
        ], $myAccountControllerContent);

        $verificationControllerContentNew = str_replace([
            '{{$name}}',
            '{{$nameSmall}}'
        ], [
            "$name",
            "$nameSmall"
        ], $verificationControllerContent);

        $controllerFile = $nameFolder.DIRECTORY_SEPARATOR."Controller.php";
        $homeFile = $nameFolder.DIRECTORY_SEPARATOR."DashboardController.php";
        $loginFile = $authFolder.DIRECTORY_SEPARATOR."LoginController.php";
        $forgotFile = $authFolder.DIRECTORY_SEPARATOR."ForgotPasswordController.php";
        $registerFile = $authFolder.DIRECTORY_SEPARATOR."RegisterController.php";
        $resetFile = $authFolder.DIRECTORY_SEPARATOR."ResetPasswordController.php";
        $verificationFile = $authFolder.DIRECTORY_SEPARATOR."VerificationController.php";

        $myAccountFile = $authFolder.DIRECTORY_SEPARATOR."{$name}AccountController.php";


        file_put_contents($controllerFile, $controllerFileContentNew);
        file_put_contents($homeFile, $homeFileContentNew);
        file_put_contents($loginFile, $loginFileContentNew);
        file_put_contents($forgotFile, $forgotFileContentNew);
        file_put_contents($registerFile, $registerFileContentNew);
        file_put_contents($resetFile, $resetFileContentNew);
        file_put_contents($myAccountFile, $myAccountFileContentNew);
        file_put_contents($verificationFile, $verificationControllerContentNew);

        return true;

    }

    /**
     * Install Requests.
     *
     * @return boolean
     */

    public function installRequests()
    {
        $nameSmall = snake_case($this->getParsedNameInput());
        $name = ucfirst($this->getParsedNameInput());

        $nameFolder = $this->getControllersPath().DIRECTORY_SEPARATOR.$name;
        if (!file_exists($nameFolder)) {
            mkdir($nameFolder);
        }

        $requestsFolder = $nameFolder.DIRECTORY_SEPARATOR."Requests";
        if (!file_exists($requestsFolder)) {
            mkdir($requestsFolder);
        }
        $accountInfoContent = file_get_contents(__DIR__ . '/../Stubs/Request/AccountInfoRequest.stub');
        $changePasswordContent = file_get_contents(__DIR__ . '/../Stubs/Request/ChangePasswordRequest.stub');

        $accountInfoContentNew = str_replace([
            '{{$name}}',
            '{{$nameSmall}}'
        ], [
            "$name",
            "$nameSmall"
        ], $accountInfoContent);

        $changePasswordContentNew = str_replace([
            '{{$name}}',
            '{{$nameSmall}}'
        ], [
            "$name",
            "$nameSmall"
        ], $changePasswordContent);

        $accountInfoFile = $requestsFolder.DIRECTORY_SEPARATOR."{$name}AccountInfoRequest.php";
        $changePasswordFile = $requestsFolder.DIRECTORY_SEPARATOR."{$name}ChangePasswordRequest.php";

        file_put_contents($accountInfoFile, $accountInfoContentNew);
        file_put_contents($changePasswordFile, $changePasswordContentNew);

        return true;

    }

    /**
     * Install Configs.
     *
     * @return boolean
     */

    public function installConfigs()
    {
        $nameSmall = snake_case($this->getParsedNameInput());
        $nameSmallPlural = str_plural(snake_case($this->getParsedNameInput()));
        $name = ucfirst($this->getParsedNameInput());

        $authConfigFile = $this->getConfigsFolderPath().DIRECTORY_SEPARATOR."auth.php";

        $guardContent = file_get_contents(__DIR__ . '/../Stubs/Config/guard.stub');
        $passwordContent = file_get_contents(__DIR__ . '/../Stubs/Config/password.stub');
        $providerContent = file_get_contents(__DIR__ . '/../Stubs/Config/provider.stub');

        $guardFileContentNew = str_replace([
            '{{$nameSmall}}',
            '{{$nameSmallPlural}}'
        ], [
            "$nameSmall",
            "$nameSmallPlural"
        ], $guardContent);

        $passwordFileContentNew = str_replace('{{$nameSmallPlural}}', "$nameSmallPlural", $passwordContent);

        $providerFileContentNew = str_replace([
            '{{$name}}',
            '{{$nameSmallPlural}}'
        ], [
            "$name",
            "$nameSmallPlural"
        ], $providerContent);

        $this->insert($authConfigFile, '    \'guards\' => [', $guardFileContentNew, true);

        $this->insert($authConfigFile, '    \'passwords\' => [', $passwordFileContentNew, true);

        $this->insert($authConfigFile, '    \'providers\' => [', $providerFileContentNew, true);

        return true;

    }

    /**
     * Install Middleware.
     *
     * @return boolean
     */

    public function installMiddleware()
    {
        $nameSmall = snake_case($this->getParsedNameInput());

        $redirectIfMiddlewareFile = $this->getMiddlewarePath().DIRECTORY_SEPARATOR."RedirectIfAuthenticated.php";
        $authenticateMiddlewareFile = $this->getMiddlewarePath().DIRECTORY_SEPARATOR."Authenticate.php";
        $ensureEmailIsVerifiedMiddlewareFile = $this->getMiddlewarePath().DIRECTORY_SEPARATOR."EnsureEmailIsVerified.php";
        $middlewareKernelFile = $this->getHttpPath().DIRECTORY_SEPARATOR."Kernel.php";

        $redirectIfMiddlewareFileContent = file_get_contents($redirectIfMiddlewareFile);
        $authenticateMiddlewareFileContent = file_get_contents($redirectIfMiddlewareFile);

        $ensureEmailIsVerifiedMiddlewareFileeContent = file_get_contents(__DIR__ . '/../Stubs/Middleware/ensureEmailIsVerified.stub');
        $redirectIfMiddlewareContentNew = file_get_contents(__DIR__ . '/../Stubs/Middleware/redirectIf.stub');
        $authenticateMiddlewareContentNew = file_get_contents(__DIR__ . '/../Stubs/Middleware/authenticate.stub');

        if (!file_exists($ensureEmailIsVerifiedMiddlewareFile)) {
            file_put_contents($ensureEmailIsVerifiedMiddlewareFile, $ensureEmailIsVerifiedMiddlewareFileeContent);
        }

        if (!str_contains($redirectIfMiddlewareFileContent, 'MultiAuthGuards')) {
            // replace old file
            $deleted = unlink($redirectIfMiddlewareFile);
            if ($deleted) {
                file_put_contents($redirectIfMiddlewareFile, $redirectIfMiddlewareContentNew);
            }
        }

        if (!str_contains($authenticateMiddlewareFileContent, 'MultiAuthGuards')) {
            // replace old file
            $deleted = unlink($authenticateMiddlewareFile);
            if ($deleted) {
                file_put_contents($authenticateMiddlewareFile, $authenticateMiddlewareContentNew);
            }
        }

        $redirectIfMiddlewareGroupContentNew = file_get_contents(__DIR__ . '/../Stubs/Middleware/redirectMiddleware.stub');
        $redirectIfMiddlewareGuardContentNew = file_get_contents(__DIR__ . '/../Stubs/Middleware/redirectMiddlewareGuard.stub');
        $authenticateIfMiddlewareGuardContentNew = file_get_contents(__DIR__ . '/../Stubs/Middleware/authenticateIf.stub');

        $redirectIfMiddlewareGroupContentNew2 = str_replace('{{$nameSmall}}', "$nameSmall",
            $redirectIfMiddlewareGroupContentNew);
        $redirectIfMiddlewareGuardContentNew2 = str_replace('{{$nameSmall}}', "$nameSmall",
            $redirectIfMiddlewareGuardContentNew);
        $authenticateIfMiddlewareGuardContentNew2 = str_replace('{{$nameSmall}}', "$nameSmall",
            $authenticateIfMiddlewareGuardContentNew);

        $this->insert($middlewareKernelFile, '    protected $middlewareGroups = [',
            $redirectIfMiddlewareGroupContentNew2, true);

        $this->insert($redirectIfMiddlewareFile, '        switch ($guard) {',
            $redirectIfMiddlewareGuardContentNew2, true);

        $this->insert($authenticateMiddlewareFile, '        // MultiAuthGuards',
            $authenticateIfMiddlewareGuardContentNew2, true);

        return true;

    }

    /**
     * Install Unauthenticated Handler.
     *
     * @return boolean
     */
    public function installUnauthenticated()
    {
        $nameSmall = snake_case($this->getParsedNameInput());
        $exceptionHandlerFile = $this->getAppFolderPath().DIRECTORY_SEPARATOR."Exceptions".DIRECTORY_SEPARATOR
            ."Handler.php";
        $exceptionHandlerFileContent = file_get_contents($exceptionHandlerFile);
        $exceptionHandlerFileContentNew = file_get_contents(__DIR__ . '/../Stubs/Exceptions/handlerUnauthorized.stub');


        if (!str_contains($exceptionHandlerFileContent, 'MultiAuthUnAuthenticated')) {
            // replace old file
            $deleted = unlink($exceptionHandlerFile);
            if ($deleted) {
                file_put_contents($exceptionHandlerFile, $exceptionHandlerFileContentNew);
            }
        }

        $exceptionHandlerGuardContentNew = file_get_contents(__DIR__ . '/../Stubs/Exceptions/handlerGuard.stub');
        $exceptionHandlerGuardContentNew2 = str_replace('{{$nameSmall}}', "$nameSmall",
            $exceptionHandlerGuardContentNew);

        $this->insert($exceptionHandlerFile, '        switch(array_get($exception->guards(), 0)) {',
            $exceptionHandlerGuardContentNew2, true);

        return true;

    }

    /**
     * Install View.
     *
     * @param string $theme_name
     */
    public function installView($theme_name = 'adminlte2')
    {

        $nameSmall = snake_case($this->getParsedNameInput());

        // layouts
        $layoutBlade = file_get_contents(__DIR__ . '/../Stubs/Views/'.$theme_name.'/layouts/layout.blade.stub');
        $layoutGuestBlade = file_get_contents(__DIR__ . '/../Stubs/Views/'.$theme_name.'/layouts/layout_guest.blade.stub');

        // dashboard
        $dashboardBlade = file_get_contents(__DIR__ . '/../Stubs/Views/'.$theme_name.'/dashboard.blade.stub');

        // auth
        $loginBlade = file_get_contents(__DIR__ . '/../Stubs/Views/'.$theme_name.'/auth/login.blade.stub');
        $registerBlade = file_get_contents(__DIR__ . '/../Stubs/Views/'.$theme_name.'/auth/register.blade.stub');
        $verifyEmailBlade = file_get_contents(__DIR__ . '/../Stubs/Views/'.$theme_name.'/auth/verify.blade.stub');

        // auth/passwords
        $resetBlade = file_get_contents(__DIR__ . '/../Stubs/Views/'.$theme_name.'/auth/passwords/reset.blade.stub');
        $emailBlade = file_get_contents(__DIR__ . '/../Stubs/Views/'.$theme_name.'/auth/passwords/email.blade.stub');

        // auth/account
        $update_infoBlade = file_get_contents(__DIR__
            . '/../Stubs/Views/'.$theme_name.'/auth/account/update_info.blade.stub');
        $right_boxBlade = file_get_contents(__DIR__
            . '/../Stubs/Views/'.$theme_name.'/auth/account/right_box.blade.stub');
        $left_boxBlade = file_get_contents(__DIR__
            . '/../Stubs/Views/'.$theme_name.'/auth/account/left_box.blade.stub');
        $change_passwordBlade = file_get_contents(__DIR__
            . '/../Stubs/Views/'.$theme_name.'/auth/account/change_password_tab.blade.stub');
        $account_infoBlade = file_get_contents(__DIR__
            . '/../Stubs/Views/'.$theme_name.'/auth/account/account_info_tab.blade.stub');

        // inc
        $alertsBlade = file_get_contents(__DIR__
            . '/../Stubs/Views/'.$theme_name.'/layouts/inc/alerts.blade.stub');
        $breadcrumbBlade = file_get_contents(__DIR__
            . '/../Stubs/Views/'.$theme_name.'/layouts/inc/breadcrumb.blade.stub');
        $headBlade = file_get_contents(__DIR__
            . '/../Stubs/Views/'.$theme_name.'/layouts/inc/head.blade.stub');
        $scriptsBlade = file_get_contents(__DIR__
            . '/../Stubs/Views/'.$theme_name.'/layouts/inc/scripts.blade.stub');


        // main_header
        $main_headerBlade = file_get_contents(__DIR__
            . '/../Stubs/Views/'.$theme_name.'/layouts/main_header/main_header.blade.stub');
        $languagesBlade = file_get_contents(__DIR__
            . '/../Stubs/Views/'.$theme_name.'/layouts/main_header/languages.blade.stub');
        $notificationsBlade = file_get_contents(__DIR__
            . '/../Stubs/Views/'.$theme_name.'/layouts/main_header/notifications.blade.stub');
        $userBlade = file_get_contents(__DIR__
            . '/../Stubs/Views/'.$theme_name.'/layouts/main_header/user.blade.stub');

        // sidemenu
        $itemsBlade = file_get_contents(__DIR__
            . '/../Stubs/Views/'.$theme_name.'/layouts/sidemenu/items.blade.stub');
        $listBlade = file_get_contents(__DIR__
            . '/../Stubs/Views/'.$theme_name.'/layouts/sidemenu/list.blade.stub');


        $createdFolders = $this->createViewsFolders($nameSmall);


        file_put_contents($createdFolders[4].'/layout.blade.php', str_replace([
            '{{$nameSmall}}',
        ], [
            $nameSmall
        ], $layoutBlade));

        file_put_contents($createdFolders[4].'/layout_guest.blade.php', str_replace([
            '{{$nameSmall}}',
        ], [
            $nameSmall
        ], $layoutGuestBlade));

        file_put_contents($createdFolders[0].'/dashboard.blade.php', str_replace([
            '{{$nameSmall}}',
        ], [
            $nameSmall
        ], $dashboardBlade));

        file_put_contents($createdFolders[1].'/login.blade.php', str_replace([
            '{{$nameSmall}}',
        ], [
            $nameSmall
        ], $loginBlade));

        file_put_contents($createdFolders[1].'/register.blade.php', str_replace([
            '{{$nameSmall}}',
        ], [
            $nameSmall
        ], $registerBlade));

        file_put_contents($createdFolders[1].'/verify.blade.php', str_replace([
            '{{$nameSmall}}',
        ], [
            $nameSmall
        ], $verifyEmailBlade));

        file_put_contents($createdFolders[2].'/reset.blade.php', str_replace([
            '{{$nameSmall}}',
        ], [
            $nameSmall
        ], $resetBlade));

        file_put_contents($createdFolders[2].'/email.blade.php', str_replace([
            '{{$nameSmall}}',
        ], [
            $nameSmall
        ], $emailBlade));

        file_put_contents($createdFolders[3].'/update_info.blade.php', str_replace([
            '{{$nameSmall}}',
        ], [
            $nameSmall
        ], $update_infoBlade));

        file_put_contents($createdFolders[3].'/right_box.blade.php', str_replace([
            '{{$nameSmall}}',
        ], [
            $nameSmall
        ], $right_boxBlade));

        file_put_contents($createdFolders[3].'/left_box.blade.php', str_replace([
            '{{$nameSmall}}',
        ], [
            $nameSmall
        ], $left_boxBlade));

        file_put_contents($createdFolders[3].'/change_password_tab.blade.php', str_replace([
            '{{$nameSmall}}',
        ], [
            $nameSmall
        ], $change_passwordBlade));

        file_put_contents($createdFolders[3].'/account_info_tab.blade.php', str_replace([
            '{{$nameSmall}}',
        ], [
            $nameSmall
        ], $account_infoBlade));

        file_put_contents($createdFolders[5].'/alerts.blade.php', str_replace([
            '{{$nameSmall}}',
        ], [
            $nameSmall
        ], $alertsBlade));

        file_put_contents($createdFolders[5].'/breadcrumb.blade.php', str_replace([
            '{{$nameSmall}}',
        ], [
            $nameSmall
        ], $breadcrumbBlade));

        file_put_contents($createdFolders[5].'/head.blade.php', str_replace([
            '{{$nameSmall}}',
        ], [
            $nameSmall
        ], $headBlade));

        file_put_contents($createdFolders[5].'/scripts.blade.php', str_replace([
            '{{$nameSmall}}',
        ], [
            $nameSmall
        ], $scriptsBlade));

        file_put_contents($createdFolders[6].'/languages.blade.php', str_replace([
            '{{$nameSmall}}',
        ], [
            $nameSmall
        ], $languagesBlade));

        file_put_contents($createdFolders[6].'/main_header.blade.php', str_replace([
            '{{$nameSmall}}',
        ], [
            $nameSmall
        ], $main_headerBlade));

        file_put_contents($createdFolders[6].'/notifications.blade.php', str_replace([
            '{{$nameSmall}}',
        ], [
            $nameSmall
        ], $notificationsBlade));

        file_put_contents($createdFolders[6].'/user.blade.php', str_replace([
            '{{$nameSmall}}',
        ], [
            $nameSmall
        ], $userBlade));

        file_put_contents($createdFolders[7].'/items.blade.php', str_replace([
            '{{$nameSmall}}',
        ], [
            $nameSmall
        ], $itemsBlade));

        file_put_contents($createdFolders[7].'/list.blade.php', str_replace([
            '{{$nameSmall}}',
        ], [
            $nameSmall
        ], $listBlade));

    }

    /**
     * @param string $nameSmall
     * @return string[]
     */
    protected function createViewsFolders($nameSmall) {

        // main guard folder
        $createFolder = $this->getViewsFolderPath().DIRECTORY_SEPARATOR."$nameSmall";
        if (!file_exists($createFolder)) {
            mkdir($createFolder);
        }

        // Auth folder
        $createFolderAuth = $this->getViewsFolderPath().DIRECTORY_SEPARATOR."$nameSmall"
            .DIRECTORY_SEPARATOR."auth";
        if (!file_exists($createFolderAuth)) {
            mkdir($createFolderAuth);
        }

        $createFolderAuthPasswords = $createFolderAuth.DIRECTORY_SEPARATOR."passwords";
        if (!file_exists($createFolderAuthPasswords)) {
            mkdir($createFolderAuthPasswords);
        }

        $createFolderAuthAccount = $createFolderAuth.DIRECTORY_SEPARATOR."account";
        if (!file_exists($createFolderAuthAccount)) {
            mkdir($createFolderAuthAccount);
        }

        // Layout folder
        $createFolderLayouts = $this->getViewsFolderPath().DIRECTORY_SEPARATOR
            ."$nameSmall"
            .DIRECTORY_SEPARATOR."layouts";
        if (!file_exists($createFolderLayouts)) {
            mkdir($createFolderLayouts);
        }

        $createFolderLayoutsInc = $createFolderLayouts .DIRECTORY_SEPARATOR."inc";
        if (!file_exists($createFolderLayoutsInc)) {
            mkdir($createFolderLayoutsInc);
        }

        $createFolderLayoutsMainHeader = $createFolderLayouts .DIRECTORY_SEPARATOR."main_header";
        if (!file_exists($createFolderLayoutsMainHeader)) {
            mkdir($createFolderLayoutsMainHeader);
        }

        $createFolderLayoutsSideMenu = $createFolderLayouts .DIRECTORY_SEPARATOR."sidemenu";
        if (!file_exists($createFolderLayoutsSideMenu)) {
            mkdir($createFolderLayoutsSideMenu);
        }

        return [
            $createFolder,
            $createFolderAuth,
            $createFolderAuthPasswords,
            $createFolderAuthAccount,

            $createFolderLayouts,
            $createFolderLayoutsInc,
            $createFolderLayoutsMainHeader,
            $createFolderLayoutsSideMenu
        ];

    }

    /**
     * Install Lang.
     *
     * @return boolean
     */

    public function installLangs()
    {
        $nameSmall = snake_case($this->getParsedNameInput());

        $dashboardLangFile = file_get_contents(__DIR__ . '/../Stubs/Languages/dashboard.stub');
        file_put_contents($this->getLangsFolderPath().'/'.$nameSmall.'_dashboard.php', $dashboardLangFile);

        return true;

    }

    /**
     * Install Project Config.
     *
     * @param string $theme_name
     * @return bool
     */
    public function installProjectConfig($theme_name = 'adminlte2')
    {
        $nameSmall = snake_case($this->getParsedNameInput());

        $projectConfigFile = file_get_contents(__DIR__ . '/../Stubs/Config/config.stub');


        file_put_contents($this->getConfigsFolderPath().'/'.$nameSmall.'_config.php', str_replace([
            '{{$theme_name}}'],
            [$theme_name],
            $projectConfigFile));

        return true;

    }

    /**
     * Install panel files under public folder
     * if developer requested free theme
     *
     * @param string $theme_name
     * @return bool
     */
    public function installPublicFilesIfNeeded($theme_name = 'adminlte2') {

        $publicPath = $this->getPublicFolderPath();
        $themePublicPath = $publicPath.DIRECTORY_SEPARATOR.$theme_name;
        if (!file_exists($themePublicPath)) {
            $githubLink = $this->getGitLinkForFreeTheme($theme_name);
            if (!is_null($githubLink) && is_string($githubLink)) {
                $zipFileName = basename($githubLink);
                $zipFile = file_get_contents($githubLink);
                $publicPath = $this->getPublicFolderPath();
                $zipFilePath = $publicPath.DIRECTORY_SEPARATOR.$zipFileName;
                file_put_contents($zipFilePath, $zipFile);
                $extracted = $this->unzipThemeFile($zipFilePath, $publicPath);
                if ($extracted) {
                    $renamed = false;
                    if ($theme_name === 'adminlte2') {
                        $adminLte2Path = $publicPath.DIRECTORY_SEPARATOR."AdminLTE-master";
                        $renamed = rename($adminLte2Path, $themePublicPath);
                    } else if ($theme_name === 'tabler') {
                        $tablerPath = $publicPath.DIRECTORY_SEPARATOR."tabler-master";
                        $renamed = rename($tablerPath, $themePublicPath);
                    }
                    return $renamed;
                }
            }
        }
        return false;
    }

    /**
     * @param $zipFile
     * @param $outputPath
     * @return bool
     */
    public function unzipThemeFile($zipFile, $outputPath) {
        $zip = new \ZipArchive();
        $res = $zip->open($zipFile);
        if ($res === TRUE) {
            $extracted = $zip->extractTo($outputPath);
            $zip->close();
            return $extracted;
        } else {
            return false;
        }
    }
    /**
     * Publish Prologue Alert
     *
     * @return boolean
     */
    public function installPrologueAlert() {
        $alertsConfigFile = $this->getConfigsFolderPath().DIRECTORY_SEPARATOR."prologue/alerts.php";
        if (!file_exists($alertsConfigFile)) {
            $this->executeProcess('php artisan vendor:publish --provider="Prologue\Alerts\AlertsServiceProvider"',
                'publishing config for notifications - prologue/alerts');
        }

        return true;
    }

    /**
     * Creating notifications table if not exists
     */
    public function installNotificationsDatabase() {
        $notificationsTableFile = $this->getMigrationPath().DIRECTORY_SEPARATOR."*_create_notifications_table.php";
        $globArray = glob($notificationsTableFile);
        if (count($globArray) < 1) {
            $this->executeProcess('php artisan notifications:table',
                'creating notifications table');
        }

    }

    /**
     * Run a SSH command.
     *
     * @param string $command
     * @param string $beforeNotice
     * @param string $afterNotice
     */
    public function executeProcess($command, $beforeNotice = '', $afterNotice = '')
    {
        if (!is_null($beforeNotice)) {
            $this->info('### '.$beforeNotice);
        } else {
            $this->info('### Running: '.$command);
        }
        $process = new Process($command);
        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                echo '... > '.$buffer;
            } else {
                echo 'OUT > '.$buffer;
            }
        });
        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        if (!is_null($afterNotice)) {
            $this->info('### '.$afterNotice);
        }
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getParsedNameInput()
    {
        return mb_strtolower(str_singular($this->getNameInput()));
    }
    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        $name = $this->argument('name');
        return trim($name);
    }

    /**
     * Get github link for free theme
     *
     * @param string $theme_name
     * @return mixed|null
     */
    protected function getGitLinkForFreeTheme($theme_name = 'adminlte2')
    {
        $themes = MultiAuthListThemes::githubLinksForFreeThemes();
        foreach ($themes as $theme => $link) {
            if ($theme === $theme_name) {
                return $link;
            }
        }
        return null;
    }

    /**
     * Write the migration file to disk.
     *
     * @param $name
     * @param $table
     * @param $create
     * @throws \Exception
     */
    protected function writeMigration($name, $table, $create)
    {
        $file = pathinfo($this->creator->create(
            $name, $this->getMigrationPath(), $table, $create
        ), PATHINFO_FILENAME);
        $this->line("<info>Created Migration:</info> {$file}");
    }

    /**
     * Get migration path.
     *
     * @return string
     */
    protected function getMigrationPath()
    {
        return parent::getMigrationPath();
    }

    /**
     * Get Routes Provider Path.
     *
     * @return string
     */
    protected function getRouteServicesPath()
    {
        return $this->getAppFolderPath().DIRECTORY_SEPARATOR.'Providers'.DIRECTORY_SEPARATOR.'RouteServiceProvider.php';
    }

    /**
     * Get Routes Folder Path.
     *
     * @return string
     */
    protected function getAppFolderPath()
    {
        return $this->laravel->basePath().DIRECTORY_SEPARATOR.'app';
    }

    /**
     * Get Public Folder Path.
     *
     * @return string
     */
    protected function getPublicFolderPath()
    {
        return $this->laravel->basePath().DIRECTORY_SEPARATOR.'public';
    }

    /**
     * Get Routes Folder Path.
     *
     * @return string
     */
    protected function getRoutesFolderPath()
    {
        return $this->laravel->basePath().DIRECTORY_SEPARATOR.'routes';
    }

    /**
     * Get Config Folder Path.
     *
     * @return string
     */
    protected function getConfigsFolderPath()
    {
        return $this->laravel->basePath().DIRECTORY_SEPARATOR.'config';
    }

    /**
     * Get Lang Folder Path.
     *
     * @return string
     */
    protected function getLangsFolderPath()
    {
        return $this->laravel->basePath().DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.'en';
    }

    /**
     * Get Views Folder Path.
     *
     * @return string
     */
    protected function getViewsFolderPath()
    {
        return $this->laravel->basePath().DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'views';
    }

    /**
     * Get Controllers Path.
     *
     * @return string
     */
    protected function getControllersPath()
    {
        return $this->getAppFolderPath().DIRECTORY_SEPARATOR.'Http'.DIRECTORY_SEPARATOR.'Controllers';
    }

    /**
     * Get Http Path.
     *
     * @return string
     */
    protected function getHttpPath()
    {
        return $this->getAppFolderPath().DIRECTORY_SEPARATOR.'Http';
    }

    /**
     * Get Middleware Path.
     *
     * @return string
     */
    protected function getMiddlewarePath()
    {
        return $this->getAppFolderPath().DIRECTORY_SEPARATOR.'Http'.DIRECTORY_SEPARATOR.'Middleware';
    }

    /**
     * insert text into file
     *
     * @param string $filePath
     * @param string $insertMarker
     * @param string $text
     * @param boolean $after
     *
     * @return integer
     */
    public function insertIntoFile($filePath, $insertMarker, $text, $after = true) {
        $contents = file_get_contents($filePath);
        $new_contents = preg_replace($insertMarker,($after) ? '$0' . $text : $text . '$0', $contents);
        return file_put_contents($filePath, $new_contents);
    }

    /**
     * insert text into file
     *
     * @param string $filePath
     * @param string $keyword
     * @param string $body
     * @param boolean $after
     *
     * @return integer
     */
    public function insert($filePath, $keyword, $body, $after = true) {

        $contents = file_get_contents($filePath);
        $new_contents = substr_replace($contents, PHP_EOL . $body,
            ($after) ? strpos($contents, $keyword) + strlen($keyword) : strpos($contents, $keyword)
            , 0);
        return file_put_contents($filePath, $new_contents);
    }
}
