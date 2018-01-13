<?php

namespace iMokhles\MultiAuthCommand\Command;

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
    protected $signature = 'make:multi-auth {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create MultiAuth for your project';

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

        $this->info("### Preparing For MultiAuth. Please wait...");
        $this->installMigration();
        $this->installModel();
        $this->installRouteMaps();
        $this->installRouteFiles();
        $this->installControllers();
        $this->installRequests();
        $this->installConfigs();
        $this->installMiddleware();
        $this->installUnauthenticated();
        $this->installView();
        $this->installPrologueAlert();
        $this->composer->dumpAutoloads();
        $this->info("### Finished MultiAuth.");

        return true;
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
     * Install Migration.
     *
     * @return boolean
     */
    public function installMigration()
    {
        $nameSmallPlural = str_plural(snake_case($this->getParsedNameInput()));
        $name = ucfirst($this->getParsedNameInput());
        $namePlural = str_plural($name);



        $modelTableContent = file_get_contents(__DIR__ . '/../Migration/modelTable.stub');
        $modelTableContentNew = str_replace([
            '{{$namePlural}}',
            '{{$nameSmallPlural}}',
        ], [
            $namePlural,
            $nameSmallPlural
        ], $modelTableContent);


        $modelResetPasswordTableContent = file_get_contents(__DIR__ . '/../Migration/passwordResetsTable.stub');
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


        $modelContent = file_get_contents(__DIR__ . '/../Model/model.stub');
        $modelContentNew = str_replace([
            '{{$name}}',
        ], [
            $name,
        ], $modelContent);
        $modelPath = $this->getAppFolderPath().DIRECTORY_SEPARATOR.$name.".php";
        file_put_contents($modelPath, $modelContentNew);


        $resetNotificationContent = file_get_contents(__DIR__ . '/../Notification/resetPasswordNotification.stub');
        $resetNotificationContentNew = str_replace([
            '{{$name}}',
            '{{$nameSmall}}',
        ], [
            $name,
            $nameSmall
        ], $resetNotificationContent);

        $createFolder = $this->getAppFolderPath().DIRECTORY_SEPARATOR."Notifications";
        if (!file_exists($createFolder)) {
            mkdir($createFolder);
        }

        $resetNotificationPath = $createFolder.DIRECTORY_SEPARATOR.$name."ResetPasswordNotification.php";
        file_put_contents($resetNotificationPath, $resetNotificationContentNew);

        return true;

    }

    /**
     * Install View.
     *
     * @return boolean
     */
    public function installView()
    {
        $nameSmall = snake_case($this->getParsedNameInput());
        $name = ucfirst($this->getParsedNameInput());

        $appBlade = file_get_contents(__DIR__ . '/../Views/layouts/app.blade.stub');
        $welcomeBlade = file_get_contents(__DIR__ . '/../Views/welcome.blade.stub');
        $homeBlade = file_get_contents(__DIR__ . '/../Views/home.blade.stub');
        $loginBlade = file_get_contents(__DIR__ . '/../Views/auth/login.blade.stub');
        $registerBlade = file_get_contents(__DIR__ . '/../Views/auth/register.blade.stub');
        $resetBlade = file_get_contents(__DIR__ . '/../Views/auth/passwords/reset.blade.stub');
        $emailBlade = file_get_contents(__DIR__ . '/../Views/auth/passwords/email.blade.stub');

        $update_infoBlade = file_get_contents(__DIR__
            . '/../Views/auth/account/update_info.blade.stub');
        $change_passwordBlade = file_get_contents(__DIR__
            . '/../Views/auth/account/change_password.blade.stub');

        $createFolder = $this->getViewsFolderPath().DIRECTORY_SEPARATOR."$nameSmall";
        if (!file_exists($createFolder)) {
            mkdir($createFolder);
        }

        $createFolderLayouts = $this->getViewsFolderPath().DIRECTORY_SEPARATOR
            ."$nameSmall"
            .DIRECTORY_SEPARATOR."layouts";
        if (!file_exists($createFolderLayouts)) {
            mkdir($createFolderLayouts);
        }

        $createFolderAuth = $this->getViewsFolderPath().DIRECTORY_SEPARATOR."$nameSmall"
            .DIRECTORY_SEPARATOR."auth";
        if (!file_exists($createFolderAuth)) {
            mkdir($createFolderAuth);
        }

        $createFolderAuthPasswords = $this->getViewsFolderPath().DIRECTORY_SEPARATOR.
            "$nameSmall".DIRECTORY_SEPARATOR
            ."auth".DIRECTORY_SEPARATOR."passwords";
        if (!file_exists($createFolderAuthPasswords)) {
            mkdir($createFolderAuthPasswords);
        }

        $createFolderAuthAccount = $this->getViewsFolderPath().DIRECTORY_SEPARATOR.
            "$nameSmall".DIRECTORY_SEPARATOR
            ."auth".DIRECTORY_SEPARATOR."account";
        if (!file_exists($createFolderAuthAccount)) {
            mkdir($createFolderAuthAccount);
        }

        $appBladeNew = str_replace([
            '{{$nameSmall}}',
        ], [
            $nameSmall
        ], $appBlade);

        $welcomeBladeNew = str_replace([
            '{{$nameSmall}}',
        ], [
            $nameSmall
        ], $welcomeBlade);

        $homeBladeNew = str_replace([
            '{{$nameSmall}}',
            '{{$name}}',

        ], [
            $nameSmall
        ], $homeBlade);

        $loginBladeNew = str_replace([
            '{{$nameSmall}}',
        ], [
            $nameSmall
        ], $loginBlade);

        $registerBladeNew = str_replace([
            '{{$nameSmall}}',
        ], [
            $nameSmall
        ], $registerBlade);

        $emailBladeNew = str_replace([
            '{{$nameSmall}}',
        ], [
            $nameSmall
        ], $emailBlade);

        $resetBladeNew = str_replace([
            '{{$nameSmall}}',
        ], [
            $nameSmall
        ], $resetBlade);

        $update_infoBladeNew = str_replace([
            '{{$nameSmall}}',
            '{{$name}}',
        ], [
            $nameSmall,
            $name
        ], $update_infoBlade);

        $change_passwordBladeNew = str_replace([
            '{{$nameSmall}}',
        ], [
            $nameSmall,
            $name
        ], $change_passwordBlade);



        file_put_contents($createFolderLayouts.'/app.blade.php', $appBladeNew);
        file_put_contents($createFolder.'/welcome.blade.php', $welcomeBladeNew);
        file_put_contents($createFolder.'/home.blade.php', $homeBladeNew);
        file_put_contents($createFolderAuth.'/login.blade.php', $loginBladeNew);
        file_put_contents($createFolderAuth.'/register.blade.php', $registerBladeNew);
        file_put_contents($createFolderAuthPasswords.'/email.blade.php', $emailBladeNew);
        file_put_contents($createFolderAuthPasswords.'/reset.blade.php', $resetBladeNew);

        file_put_contents($createFolderAuthAccount.'/update_info.blade.php', $update_infoBladeNew);
        file_put_contents($createFolderAuthAccount.'/change_password.blade.php', $change_passwordBladeNew);

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
        $mapCallFunction = file_get_contents(__DIR__ . '/../Route/mapRoute.stub');
        $mapCallFunctionNew = str_replace('{{$name}}', "$name", $mapCallFunction);
        $this->insert($this->getRouteServicesPath(), '$this->mapWebRoutes();', $mapCallFunctionNew, true);
        $mapFunction = file_get_contents(__DIR__ . '/../Route/mapRouteFunction.stub');
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
        $routeFileContent = file_get_contents(__DIR__ . '/../Route/routeFile.stub');
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
        $accountInfoContent = file_get_contents(__DIR__ . '/../Request/AccountInfoRequest.stub');
        $changePasswordContent = file_get_contents(__DIR__ . '/../Request/ChangePasswordRequest.stub');

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

        $controllerContent = file_get_contents(__DIR__ . '/../Controllers/Controller.stub');
        $homeControllerContent = file_get_contents(__DIR__ . '/../Controllers/HomeController.stub');
        $loginControllerContent = file_get_contents(__DIR__ . '/../Controllers/Auth/LoginController.stub');
        $forgotControllerContent = file_get_contents(__DIR__ . '/../Controllers/Auth/ForgotPasswordController.stub');
        $registerControllerContent = file_get_contents(__DIR__ . '/../Controllers/Auth/RegisterController.stub');
        $resetControllerContent = file_get_contents(__DIR__ . '/../Controllers/Auth/ResetPasswordController.stub');
        $myAccountControllerContent = file_get_contents(__DIR__ . '/../Controllers/Auth/MyAccountController.stub');

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

        $controllerFile = $nameFolder.DIRECTORY_SEPARATOR."Controller.php";
        $homeFile = $nameFolder.DIRECTORY_SEPARATOR."HomeController.php";
        $loginFile = $authFolder.DIRECTORY_SEPARATOR."LoginController.php";
        $forgotFile = $authFolder.DIRECTORY_SEPARATOR."ForgotPasswordController.php";
        $registerFile = $authFolder.DIRECTORY_SEPARATOR."RegisterController.php";
        $resetFile = $authFolder.DIRECTORY_SEPARATOR."ResetPasswordController.php";

        $myAccountFile = $authFolder.DIRECTORY_SEPARATOR."{$name}AccountController.php";


        file_put_contents($controllerFile, $controllerFileContentNew);
        file_put_contents($homeFile, $homeFileContentNew);
        file_put_contents($loginFile, $loginFileContentNew);
        file_put_contents($forgotFile, $forgotFileContentNew);
        file_put_contents($registerFile, $registerFileContentNew);
        file_put_contents($resetFile, $resetFileContentNew);
        file_put_contents($myAccountFile, $myAccountFileContentNew);

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

        $guardContent = file_get_contents(__DIR__ . '/../Config/guard.stub');
        $passwordContent = file_get_contents(__DIR__ . '/../Config/password.stub');
        $providerContent = file_get_contents(__DIR__ . '/../Config/provider.stub');

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
        $exceptionHandlerFileContentNew = file_get_contents(__DIR__ . '/../Exceptions/handlerUnauthorized.stub');


        if (!str_contains($exceptionHandlerFileContent, 'MultiAuthUnAuthenticated')) {
            // replace old file
            $deleted = unlink($exceptionHandlerFile);
            if ($deleted) {
                file_put_contents($exceptionHandlerFile, $exceptionHandlerFileContentNew);
            }
        }

        $exceptionHandlerGuardContentNew = file_get_contents(__DIR__ . '/../Exceptions/handlerGuard.stub');
        $exceptionHandlerGuardContentNew2 = str_replace('{{$nameSmall}}', "$nameSmall",
            $exceptionHandlerGuardContentNew);

        $this->insert($exceptionHandlerFile, '        switch(array_get($exception->guards(), 0)) {',
            $exceptionHandlerGuardContentNew2, true);

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
        $middlewareKernelFile = $this->getHttpPath().DIRECTORY_SEPARATOR."Kernel.php";

        $redirectIfMiddlewareFileContent = file_get_contents($redirectIfMiddlewareFile);

        $redirectIfMiddlewareContentNew = file_get_contents(__DIR__ . '/../Middleware/redirectIf.stub');

        if (!str_contains($redirectIfMiddlewareFileContent, 'MultiAuthGuards')) {
            // replace old file
            $deleted = unlink($redirectIfMiddlewareFile);
            if ($deleted) {
                file_put_contents($redirectIfMiddlewareFile, $redirectIfMiddlewareContentNew);
            }
        }

        $redirectIfMiddlewareGroupContentNew = file_get_contents(__DIR__ . '/../Middleware/redirectMiddleware.stub');
        $redirectIfMiddlewareGuardContentNew = file_get_contents(__DIR__ . '/../Middleware/redirectMiddlewareGuard.stub');

        $redirectIfMiddlewareGroupContentNew2 = str_replace('{{$nameSmall}}', "$nameSmall",
            $redirectIfMiddlewareGroupContentNew);
        $redirectIfMiddlewareGuardContentNew2 = str_replace('{{$nameSmall}}', "$nameSmall",
            $redirectIfMiddlewareGuardContentNew);

        $this->insert($middlewareKernelFile, '    protected $middlewareGroups = [',
            $redirectIfMiddlewareGroupContentNew2, true);

        $this->insert($redirectIfMiddlewareFile, '        switch ($guard) {',
            $redirectIfMiddlewareGuardContentNew2, true);

        return true;

    }

    /**
     * Run a SSH command.
     *
     * @param string $command      The SSH command that needs to be run
     * @param bool   $beforeNotice Information for the user before the command is run
     * @param bool   $afterNotice  Information for the user after the command is run
     *
     * @return mixed Command-line output
     */
    public function executeProcess($command, $beforeNotice = false, $afterNotice = false)
    {
        if ($beforeNotice) {
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
        if ($afterNotice) {
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
        return trim($this->argument('name'));
    }

    /**
     * Write the migration file to disk.
     *
     * @param  string  $name
     * @param  string  $table
     * @param  bool    $create
     * @return mixed
     */
    protected function writeMigration($name, $table, $create)
    {
        $file = pathinfo($this->creator->create(
            $name, $this->getMigrationPath(), $table, $create
        ), PATHINFO_FILENAME);
        $this->line("<info>Created Migration:</info> {$file}");
    }

    /**
     * Get migration path (either specified by '--path' option or default location).
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
     * Get Config Folder Path.
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
