# php-internals-mocker

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]



Util to allow mocking PHP Internal function calls in tests.


## Installation

The preferred method of installation is via [Composer](http://getcomposer.org/). Run the following command to install the latest version of a package and add it to your project's `composer.json`:

```bash
composer require idimsh/php-internals-mocker
```

## Usage

``` php

use idimsh\PhpInternalsMocker\PhpFunctionSimpleMocker;

PhpFunctionSimpleMocker::add(
    'ini_get',
    \Vendor\Package\Namespace\MyClass::class,
    function ($key) {
        static::assertSame('apc.enabled', $key);
        return true;
    }
);
```

## Credits

- [Abdulrahman Dimashki][link-author]
- [All Contributors][link-contributors]

## License

Released under MIT License - see the [License File](LICENSE) for details.


[ico-version]: https://img.shields.io/packagist/v/idimsh/php-internals-mocker.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/idimsh/php-internals-mocker/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/idimsh/php-internals-mocker.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/idimsh/php-internals-mocker.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/idimsh/php-internals-mocker.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/idimsh/php-internals-mocker
[link-travis]: https://travis-ci.org/idimsh/php-internals-mocker
[link-scrutinizer]: https://scrutinizer-ci.com/g/idimsh/php-internals-mocker/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/idimsh/php-internals-mocker
[link-downloads]: https://packagist.org/packages/idimsh/php-internals-mocker
[link-author]: https://github.com/idimsh
[link-contributors]: ../../contributors
