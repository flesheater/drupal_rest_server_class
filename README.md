# webham/drupalrest

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

This is a drupal rest component that currently works with drupal 7 instance eqiped with the services module. You should first setup
the drupal application and then with the help of this package you should be able to access the data via the user name and the password
of a created user.

## Install

Via Composer

``` bash
$ composer require webham/drupalrest
```

## Usage

``` php


$request = new DrupalRest('http://yoursite.com/', '/rest', 'user', 'pass', 0);

print_r($request);


/**
 * Login.
 */
$request->login();
print_r($request);


```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email nikolay.r.borisov@gmail.com instead of using the issue tracker.

## Credits

- [Nikolay Borisov][link-author]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/:vendor/:package_name.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/:vendor/:package_name.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/:vendor/:package_name.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/:vendor/:package_name.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/:vendor/:package_name
[link-scrutinizer]: https://scrutinizer-ci.com/g/flesheater/drupal_rest_server_class/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/flesheater/drupal_rest_server_class
[link-downloads]: https://packagist.org/packages/:vendor/:package_name
[link-author]: https://github.com/flesheater
[link-contributors]: ../../contributors