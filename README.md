# MultiAuthCommand

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/iMokhles/MultiAuthCommand/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/iMokhles/MultiAuthCommand/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/iMokhles/MultiAuthCommand/badges/build.png?b=master)](https://scrutinizer-ci.com/g/iMokhles/MultiAuthCommand/build-status/master)
[![Total Downloads][ico-downloads]][link-downloads]
[![Software License][ico-license]](LICENSE.md)

create laravel multi-auth setup files, middleware, models, migrations etc

## Install ( no need for Laravel 5.5 and above )

1. In your terminal via composer:

``` bash
composer require imokhles/multi-auth-command
```

2. Add this provider to your config/app.php :
```
iMokhles\MultiAuthCommand\MultiAuthCommandServiceProvider::class
```

## Usage

Example usage: 


``` bash
php artisan make:multi-auth Admin --force
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

[link-packagist]: https://packagist.org/packages/imokhles/multi-auth-command
[link-downloads]: https://packagist.org/packages/imokhles/multi-auth-command
[link-author]: https://github.com/imokhles