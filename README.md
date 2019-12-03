# PHP Internal function mocker

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]
[![PHP Version][ico-phpversion]][link-packagist]




Util to allow mocking PHP Internal function calls in tests.


## Installation

The preferred method of installation is via [Composer](http://getcomposer.org/). Run the following command to install the latest version of a package and add it to your project's `composer.json`:

```bash
composer require-dev idimsh/php-internals-mocker
```

## Usage

This mocker is intended to be used in Unit Tests, assume a class like this:

``` php
namespace Vendor\Namespace

class MyClass 
{
    public function openConnction($hostname) 
    {
        return fsockopen($hostname);
    }
}
```

Has to be tested with unit tests for method `openConnction()`. A PhpUnit test case would be like:

``` php
namespace VendorTest\Namespace

class MyClassTest extends \PHPUnit\Framework\TestCase
{
    public function testOpenConnction($hostname): void
    {
        $object   = new \Vendor\Namespace\MyClass;
        $hostname = \uniqid('hostname');
        $actual   = $object->openConnection($hostname);
        // ...
    }
}
```

We do not really want to open a connection especially in unit tests, so this mocker can avoid the call to the native PHP `fsockopen()` and replace it with a call to a defined callback like:
``` php
namespace VendorTest\Namespace

use idimsh\PhpInternalsMocker\PhpFunctionSimpleMocker;

class MyClassTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        PhpFunctionSimpleMocker::reset();
    }
    
    public function testOpenConnction(): void
    {
        $hostname = \uniqid('hostname');
        $return   = \uniqid('some mock for the return of fsockopen()');
        
        PhpFunctionSimpleMocker::add(
            'fsockopen',
            \Vendor\Namespace\MyClass::class,
            function ($inputHostname) use ($hostname, $return) {
                static::assertSame($inputHostname, $hostname);
                return $return;
            }
        );
        
        $object = new \Vendor\Namespace\MyClass;
        $actual = $object->openConnection($hostname);
        static::assertSame($return, $actual);

        /** @noinspection PhpUnhandledExceptionInspection */
        PhpFunctionSimpleMocker::phpUnitAssertNotEnoughCalls($this);
    }
}
```

## Methods Manual

1- `PhpFunctionSimpleMocker::reset()`: should be called in PhpUnit TestCase `setUp()` method or at the beginning of a test method.
2- `PhpFunctionSimpleMocker::add()`: to be called after `reset()` to register the callbacks expected to native functions, signature:  
``` php
    /**
     * Register a call back to be called for the PHP internal function which is to be used in the class passed.
     *
     * If the $callback is null, then this PHP function is not expected to be called.
     *
     * Assertions can be done inside the callback.
     *
     * @param string        $internalFunctionName The PHP function name to mock
     * @param string        $beingCalledFromClass The class FQN which calls $internalFunctionName
     * @param callable|null $callback
     * @param int           $numberOfCalls        To mock more than once for the same callback, pass the number here
     */
    public static function add(
        string $internalFunctionName,
        string $beingCalledFromClass,
        ?callable $callback,
        int $numberOfCalls = 1
    ): void
```

It can be called multiple times with the same `$internalFunctionName` and different `$callback` for each call in the order expected.  
The `$beingCalledFromClass` expects a class FQN which from the namespace will be extracted and the function will be registered at that namespace.  

3- `PhpFunctionSimpleMocker::phpUnitAssertNotEnoughCalls($testCase)`: To be called from PhpUnit test method after all the assertions have been registered (last line), this method will make sure that the minimum number of calls has been reached.   

3- `PhpFunctionSimpleMocker::assertPostConditions(?$testCase)`: Alternative to `PhpFunctionSimpleMocker::phpUnitAssertNotEnoughCalls($testCase)` and to be called from PhpUnit TestCase method: `assertPostConditions()`, instead of calling the previous method at the end of each Test method, a one call passing the TestCase is enough to assert minimum count.     

## Usage Conditions

The native PHP function call that is to be mocked and replaced with a callback needs to be (All must apply):
- Called from a class method or a function that is defined inside a namespace and not from a class method or a function which reside in the global namespace.
- The call that PHP native function must not be preceeded by the global namespace resolution operator '\\' 
- The `use function` statement is not used to import that native function into the namespace in the class.


## Limitations
Quickly:
- PHP native functions that use references are not supported as of now, put planned to.
- In PhpUnit, assertions for not enough calls has to be explicitly handled by calling `PhpFunctionSimpleMocker::phpUnitAssertNotEnoughCalls($this)`, if any better ideas are there please share.      
- For any strange issues, the `@runInSeparateProcess` options of PhpUnit might help, though I did not encounter such cases yet, please report if any.    

## Credits

- [Abdulrahman Dimashki][link-author]
- [All Contributors][link-contributors]
- An old [Symfony](https://github.com/symfony/symfony) class for mocking PHP Internal functions, could not find the source of it. But the code in `PhpFunctionSimpleMocker::register()` is taken from it.

## Alternatives
There is a solution I havn't tested yet [php-mock](https://github.com/php-mock/php-mock)


## License

Released under MIT License - see the [License File](LICENSE) for details.


[ico-version]: https://img.shields.io/packagist/v/idimsh/php-internals-mocker.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/idimsh/php-internals-mocker/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/idimsh/php-internals-mocker.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/idimsh/php-internals-mocker.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/idimsh/php-internals-mocker.svg?style=flat-square
[ico-phpversion]: https://img.shields.io/packagist/php-v/idimsh/php-internals-mocker?style=flat-square

[link-packagist]: https://packagist.org/packages/idimsh/php-internals-mocker
[link-travis]: https://travis-ci.org/idimsh/php-internals-mocker
[link-scrutinizer]: https://scrutinizer-ci.com/g/idimsh/php-internals-mocker/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/idimsh/php-internals-mocker
[link-downloads]: https://packagist.org/packages/idimsh/php-internals-mocker
[link-author]: https://github.com/idimsh
[link-contributors]: ../../contributors
