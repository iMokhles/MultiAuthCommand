<?php

namespace iMokhles\MultiAuthCommand\Command;

use Illuminate\Database\Console\Migrations\BaseCommand;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Support\Composer;

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
     * @return mixed
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
        $this->installMigration();
        $this->installModel();
        $this->installRouteMaps();
        $this->installRouteFiles();
        $this->installControllers();
        $this->installConfigs();
        $this->installMiddleware();
        $this->installView();
        $this->composer->dumpAutoloads();

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



        $modelTableContent = file_get_contents(__DIR__ . '/Migration/modelTable.stub');
        $modelTableContentNew = str_replace([
            '{{$namePlural}}',
            '{{$nameSmallPlural}}',
        ], [
            $namePlural,
            $nameSmallPlural
        ], $modelTableContent);


        $modelResetPasswordTableContent = file_get_contents(__DIR__ . '/Migration/passwordResetsTable.stub');
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

        $migrationResetName = date('Y_m_d_His') . '_'.'create_' . str_plural(snake_case($name)) .'_password_resets_table.php';
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


        $modelContent = file_get_contents(__DIR__ . '/Model/model.stub');
        $modelContentNew = str_replace([
            '{{$name}}',
        ], [
            $name,
        ], $modelContent);
        $modelPath = $this->getAppFolderPath().DIRECTORY_SEPARATOR.$name.".php";
        file_put_contents($modelPath, $modelContentNew);


        $resetNotificationContent = file_get_contents(__DIR__ . '/Notification/resetPasswordNotification.stub');
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


        $appBlade = file_get_contents(__DIR__ . '/Views/layouts/app.blade.stub');
        $welcomeBlade = file_get_contents(__DIR__ . '/Views/welcome.blade.stub');
        $homeBlade = file_get_contents(__DIR__ . '/Views/home.blade.stub');
        $loginBlade = file_get_contents(__DIR__ . '/Views/auth/login.blade.stub');
        $registerBlade = file_get_contents(__DIR__ . '/Views/auth/register.blade.stub');
        $resetBlade = file_get_contents(__DIR__ . '/Views/auth/passwords/reset.blade.stub');
        $emailBlade = file_get_contents(__DIR__ . '/Views/auth/passwords/email.blade.stub');

        $createFolder = $this->getViewsFolderPath().DIRECTORY_SEPARATOR."$nameSmall";
        if (!file_exists($createFolder)) {
            mkdir($createFolder);
        }

        $createFolderLayouts = $this->getViewsFolderPath().DIRECTORY_SEPARATOR."$nameSmall".DIRECTORY_SEPARATOR."layouts";
        if (!file_exists($createFolderLayouts)) {
            mkdir($createFolderLayouts);
        }

        $createFolderAuth = $this->getViewsFolderPath().DIRECTORY_SEPARATOR."$nameSmall".DIRECTORY_SEPARATOR."auth";
        if (!file_exists($createFolderAuth)) {
            mkdir($createFolderAuth);
        }

        $createFolderAuthPasswords = $this->getViewsFolderPath().DIRECTORY_SEPARATOR."$nameSmall".DIRECTORY_SEPARATOR."auth".DIRECTORY_SEPARATOR."passwords";
        if (!file_exists($createFolderAuthPasswords)) {
            mkdir($createFolderAuthPasswords);
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

        file_put_contents($createFolderLayouts.'/app.blade.php', $appBladeNew);
        file_put_contents($createFolder.'/welcome.blade.php', $welcomeBladeNew);
        file_put_contents($createFolder.'/home.blade.php', $homeBladeNew);
        file_put_contents($createFolderAuth.'/login.blade.php', $loginBladeNew);
        file_put_contents($createFolderAuth.'/register.blade.php', $registerBladeNew);
        file_put_contents($createFolderAuthPasswords.'/email.blade.php', $emailBladeNew);
        file_put_contents($createFolderAuthPasswords.'/reset.blade.php', $resetBladeNew);

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

        $mapCallFunction = file_get_contents(__DIR__ . '/Route/mapRoute.stub');
        $mapCallFunctionNew = str_replace('{{$name}}', "$name", $mapCallFunction);
        $this->insert($this->getRouteServicesPath(), '$this->mapWebRoutes();', $mapCallFunctionNew, true);


        $mapFunction = file_get_contents(__DIR__ . '/Route/mapRouteFunction.stub');
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

        $routeFileContent = file_get_contents(__DIR__ . '/Route/routeFile.stub');
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

        $controllerContent = file_get_contents(__DIR__ . '/Controllers/Controller.stub');
        $homeControllerContent = file_get_contents(__DIR__ . '/Controllers/HomeController.stub');
        $loginControllerContent = file_get_contents(__DIR__ . '/Controllers/Auth/LoginController.stub');
        $forgotControllerContent = file_get_contents(__DIR__ . '/Controllers/Auth/ForgotPasswordController.stub');
        $registerControllerContent = file_get_contents(__DIR__ . '/Controllers/Auth/RegisterController.stub');
        $resetControllerContent = file_get_contents(__DIR__ . '/Controllers/Auth/ResetPasswordController.stub');

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

        $controllerFile = $nameFolder.DIRECTORY_SEPARATOR."Controller.php";
        $homeFile = $nameFolder.DIRECTORY_SEPARATOR."HomeController.php";
        $loginFile = $authFolder.DIRECTORY_SEPARATOR."LoginController.php";
        $forgotFile = $authFolder.DIRECTORY_SEPARATOR."ForgotPasswordController.php";
        $registerFile = $authFolder.DIRECTORY_SEPARATOR."RegisterController.php";
        $resetFile = $authFolder.DIRECTORY_SEPARATOR."ResetPasswordController.php";

        file_put_contents($controllerFile, $controllerFileContentNew);
        file_put_contents($homeFile, $homeFileContentNew);
        file_put_contents($loginFile, $loginFileContentNew);
        file_put_contents($forgotFile, $forgotFileContentNew);
        file_put_contents($registerFile, $registerFileContentNew);
        file_put_contents($resetFile, $resetFileContentNew);

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

        $guardContent = file_get_contents(__DIR__ . '/Config/guard.stub');
        $passwordContent = file_get_contents(__DIR__ . '/Config/password.stub');
        $providerContent = file_get_contents(__DIR__ . '/Config/provider.stub');

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
        $middlewareKernelFile = $this->getHttpPath().DIRECTORY_SEPARATOR."Kernel.php";

        $redirectIfMiddlewareFileContent = file_get_contents($redirectIfMiddlewareFile);

        $redirectIfMiddlewareContentNew = file_get_contents(__DIR__ . '/Middleware/redirectIf.stub');

        if (!str_contains($redirectIfMiddlewareFileContent, 'MultiAuthGuards')) {
            // replace old file
            $deleted = unlink($redirectIfMiddlewareFile);
            if ($deleted) {
                file_put_contents($redirectIfMiddlewareFile, $redirectIfMiddlewareContentNew);
            }
        }

        $redirectIfMiddlewareGroupContentNew = file_get_contents(__DIR__ . '/Middleware/redirectMiddleware.stub');
        $redirectIfMiddlewareGuardContentNew = file_get_contents(__DIR__ . '/Middleware/redirectMiddlewareGuard.stub');

        $redirectIfMiddlewareGroupContentNew2 = str_replace('{{$nameSmall}}', "$nameSmall", $redirectIfMiddlewareGroupContentNew);
        $redirectIfMiddlewareGuardContentNew2 = str_replace('{{$nameSmall}}', "$nameSmall", $redirectIfMiddlewareGuardContentNew);

        $this->insert($middlewareKernelFile, '    protected $middlewareGroups = [', $redirectIfMiddlewareGroupContentNew2, true);

        $this->insert($redirectIfMiddlewareFile, '        switch ($guard) {', $redirectIfMiddlewareGuardContentNew2, true);

        return true;

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
     * @return mixed
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
     * @return mixed
     */
    public function insert($filePath, $keyword, $body, $after = true) {

        $contents = file_get_contents($filePath);
        $new_contents = substr_replace($contents, PHP_EOL . $body, ($after) ? strpos($contents, $keyword) + strlen($keyword) : strpos($contents, $keyword), 0);
        return file_put_contents($filePath, $new_contents);
    }
}
