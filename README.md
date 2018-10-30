# MultiAuthCommand

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Scrutinizer Code Quality][ico-code-quality]][link-code-quality]
[![Build Status](https://scrutinizer-ci.com/g/iMokhles/MultiAuthCommand/badges/build.png?b=master)](https://scrutinizer-ci.com/g/iMokhles/MultiAuthCommand/build-status/master)
[![Total Downloads][ico-downloads]][link-downloads]
[![Software License][ico-license]](LICENSE.md)

create laravel multi-auth guard setup files, middleware, models, migrations etc

## Install

1. In your terminal via composer:

``` bash
composer require imokhles/multi-auth-command
```

2. Add this provider to your config/app.php ( no need for Laravel 5.5 and above ) :
```
iMokhles\MultiAuthCommand\MultiAuthCommandServiceProvider::class
```

3. copy theme files to 
```
PROJECT_DIR/public/start_ui/*css,js,img,fonts
```

## Available themes

* [StartUI](https://themeforest.net/item/startui-premium-bootstrap-4-admin-dashboard-template/15228250?ref=themesanytime)
* more comes later ( and you are welcome to send me a pull request for more themes )

## Theme folder structure 

    .
    ├── Views ( folder )
    │   └── THEME_NAME ( folder )
    │       ├── auth ( folder )
    │       │    ├── account ( folder )
    │       │    │   ├── account_info_tab.blade.stub
    │       │    │   ├── change_password_tab.blade.stub
    │       │    │   ├── left_box.blade.stub
    │       │    │   ├── right_box.blade.stub
    │       │    │   └── update_info.blade.stub
    │       │    ├── passwords ( folder )
    │       │    │   ├── email.blade.stub
    │       │    │   └── reset.blade.stub
    │       │    ├── login.blade.stub
    │       │    ├── register.blade.stub
    │       │    └── verify.blade.stub
    │       ├── layouts ( folder )
    │       │     ├── inc ( folder )
    │       │     │    ├── alerts.blade.stub
    │       │     │    ├── breadcrumb.blade.stub
    │       │     │    ├── head.blade.stub
    │       │     │    └── scripts.blade.stub
    │       │     ├── main_header ( folder )
    │       │     │    ├── languages.blade.stub
    │       │     │    ├── main_header.blade.stub
    │       │     │    ├── notifications.blade.stub
    │       │     │    └── user.blade.stub
    │       │     ├── sidemenu ( folder )
    │       │     │    ├── items.blade.stub
    │       │     │    └── list.blade.stub
    │       │     ├── layout.blade.stub
    │       │     └── layout_guest.blade.stub
    │       └── dashboard.blade.stub
    └── ...

## Usage

Example usage: 


``` bash
php artisan make:multi-auth Admin --admin_theme="startui"
```

## Security

If you discover any security related issues, please email imokhles@imokhles.com instead of using the issue tracker.

## Credits

- [iMokhles](http://github.com/imokhles)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/imokhles/multi-auth-command.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/imokhles/multi-auth-command.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/iMokhles/MultiAuthCommand.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/imokhles/multi-auth-command
[link-downloads]: https://packagist.org/packages/imokhles/multi-auth-command
[link-author]: https://github.com/imokhles
[link-code-quality]: https://scrutinizer-ci.com/g/iMokhles/MultiAuthCommand

[![Beerpay](https://beerpay.io/iMokhles/MultiAuthCommand/badge.svg?style=beer-square)](https://beerpay.io/iMokhles/MultiAuthCommand)  [![Beerpay](https://beerpay.io/iMokhles/MultiAuthCommand/make-wish.svg?style=flat-square)](https://beerpay.io/iMokhles/MultiAuthCommand?focus=wish)
