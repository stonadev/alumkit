<div align="center">
    <h1>Alumkit</h1>
</div>

<p align="center">
    <a href="https://packagist.org/packages/lazycod3rs/alumkit"><img src="https://img.shields.io/packagist/v/lazycod3rs/alumkit.svg?style=flat-square" alt="Packagist"></a>
    <a href="https://packagist.org/packages/lazycod3rs/alumkit"><img src="https://img.shields.io/packagist/php-v/lazycod3rs/alumkit.svg?style=flat-square" alt="PHP from Packagist"></a>
    <a href="https://packagist.org/packages/lazycod3rs/alumkit"><img src="https://badge.laravel.cloud/badge/lazycod3rs/alumkit?style=flat" alt="Laravel versions"></a>
    <a href="https://github.com/lazycod3rs/alumkit/actions"><img alt="GitHub Workflow Status (main)" src="https://img.shields.io/github/actions/workflow/status/lazycod3rs/alumkit/tests.yml?branch=main&label=Tests&style=flat-square"></a>
    <a href="https://packagist.org/packages/lazycod3rs/alumkit"><img src="https://img.shields.io/packagist/dt/lazycod3rs/alumkit.svg?style=flat-square" alt="Total Downloads"></a>
</p>

A Laravel toolkit for alumni management applications.

## Installation

You can install the package via Composer:

```bash
composer require lazycod3rs/alumkit
```

You may publish all of the package's resources at once:

```bash
php artisan vendor:publish --tag="alumkit"
```

Or, you may publish each resource individually:

### Publishing the Configuration File

```bash
php artisan vendor:publish --tag="alumkit-config"
```

### Publishing and Running the Migrations

```bash
php artisan vendor:publish --tag="alumkit-migrations"
php artisan migrate
```

### Publishing the Views

```bash
php artisan vendor:publish --tag="alumkit-views"
```

### Publishing the Translations

```bash
php artisan vendor:publish --tag="alumkit-lang"
```

### Publishing the Public Assets

```bash
php artisan vendor:publish --tag="alumkit-assets"
```

## Usage

<!-- Add a basic usage example here. -->

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Thank you for considering contributing to Alumkit! Please review our [contributing guide](.github/CONTRIBUTING.md) to get started.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Shuvo Paul](https://github.com/lazycod3rs)
- [All Contributors](../../contributors)

## License

Alumkit is open-sourced software licensed under the [MIT license](LICENSE.md).
