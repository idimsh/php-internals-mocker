<?php
declare(strict_types=1);

namespace idimsh\PhpInternalsMocker;

/**
 * The method used will utilize namespaces, and require that the call to PHP internals is
 * 1- Not called with absolute name space: like: \date()
 * 2- Used in a class which has a namespace and not used in global namespace.
 *
 * Usage:
 *
 * 1- Example 1:
 *
 * PhpFunctionSimpleMocker::add(
 *   'ini_get',
 *   \Vendor\Package\Namespace\MyClass::class,
 *   function ($key) {
 *     static::assertSame('apc.enabled', $key);
 *     return true;
 *   }
 * );
 *
 * Will register a call to ini_get() from within ANY Method of ANY Class inside the namespace:
 * \Vendor\Package\Namespace\
 *
 *
 * 2- Example 2:
 *
 * PhpFunctionSimpleMocker::add(
 *   'register_shutdown_function',
 *   \Vendor\Package\Namespace\MyClass::class,
 *   null
 * );
 *
 * Will assure that register_shutdown_function() will never be called from any method inside that namespace
 *
 * @codeCoverageIgnore
 * @reason Class uses static methods and is used in tests only.
 */
class PhpFunctionSimpleMocker
{
    /**
     * [
     *   {internal function name} => [ array of callable(s) ],
     *   ...
     * ]
     *
     * @var array | ?callable[][]
     */
    private static $registeredCallBacks = [];

    /**
     * [
     *    {internal function name} => integer (how many times it is called before),
     *    ....
     * ]
     *
     * @var array | int[]
     */
    private static $callCounters = [];


    /**
     * Reset registered callbacks and start over
     */
    public static function reset()
    {
        static::$callCounters        = [];
        static::$registeredCallBacks = [];
    }

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
    {
        static::$registeredCallBacks[$internalFunctionName] = static::$registeredCallBacks[$internalFunctionName] ?? [];
        if ($callback === null) {
            self::addNullCallback($internalFunctionName);
        }
        else {
            while (--$numberOfCalls >= 0) {
                static::$registeredCallBacks[$internalFunctionName][] = $callback;
            }
        }
        static::register($internalFunctionName, $beingCalledFromClass);
    }

    /**
     * This can be used in PHPUnit method: protected function assertPostConditions()
     * And be called from there statically to throw an exception after the test method
     * assertions has been evaluated. Which is not very elegant as the name of the test
     * method will not be available then.
     *
     * @throws Exception\NotEnoughCalls
     */
    public static function assertPostConditions(): void
    {
        foreach (\array_keys(static::$registeredCallBacks) as $internalFunctionName) {
            $invocationCount = static::$callCounters[$internalFunctionName] ?? 0;
            $callbacks       = static::$registeredCallBacks[$internalFunctionName] ?? [];
            $countCallbacks  = \count($callbacks);
            if ($countCallbacks === 0 || \in_array(null, $callbacks, true)) {
                // these cases are not handled here, but in ::call()
                continue;
            }
            if ($invocationCount < $countCallbacks) {
                throw Exception\NotEnoughCalls::fromFunctionName($internalFunctionName, $countCallbacks, $invocationCount);
            }
        }
    }

    /**
     * This is for PhpUnit only
     * This can be (and should be) called from PhpUnit test methods. Assuming that the self::reset() method
     * is being called from the \PHPUnit\Framework\TestCase::setUp() method AND the self::add() method
     * is being called from that particular test method, then a call to this method should be invoked as the
     * last assertion in that test method.
     *
     * @param object $testCase An instance of \PHPUnit\Framework\TestCase, type is not enforced since
     *                         the class might not be available.
     *
     * @throws Exception\InvalidArgumentException
     */
    public static function phpUnitAssertNotEnoughCalls($testCase): void
    {
        if (!\class_exists('PHPUnit\Framework\TestCase')) {
            return;
        }
        if (!$testCase instanceof \PHPUnit\Framework\TestCase) {
            throw new Exception\InvalidArgumentException(
                \sprintf(
                    'Parameter to method: [%s] is expected to be an instance of: [%s], got type: [%s] which is invalid',
                    __METHOD__,
                    \PHPUnit\Framework\TestCase::class,
                    \gettype($testCase)
                )
            );
        }
        try {
            static::assertPostConditions();
        }
        catch (Exception\NotEnoughCalls $exception) {
            /** @var \PHPUnit\Framework\TestCase $testCase */
            $testCase::fail($exception->getMessage());
        }
    }

    /**
     * This must be Public, but must not be called externally.
     *
     * @param string     $internalFunctionName
     * @param null|array ...$args
     *
     * @return mixed
     * @throws Exception\CallsLimitExceeded
     * @throws Exception\NeverExpected
     * @internal
     */
    public static function call(string $internalFunctionName, &...$args)
    {
        $countCalled    = static::$callCounters[$internalFunctionName] ?? 0;
        $callbacks      = static::$registeredCallBacks[$internalFunctionName] ?? [];
        $countCallbacks = \count($callbacks);

        if ($countCallbacks === 0) {
            /**
             * We will reach here if
             * 1- the function was registered to be called then the class was reset by
             * calling self::reset(), this will not however remove the registered function in
             * the namespace requested, so here we call the original PHP function with arguments.
             *
             */
            if (is_callable($internalFunctionName)) {
                return $internalFunctionName(...$args);
            }
            return null;
        }

        if (\in_array(null, $callbacks, true)) {
            throw Exception\NeverExpected::fromFunctionName($internalFunctionName);
        }

        if ($countCalled >= $countCallbacks) {
            throw Exception\CallsLimitExceeded::fromFunctionNameLimit($internalFunctionName, $countCallbacks);
        }

        /**
         * @var $callback callable
         */
        $callback                                    = static::$registeredCallBacks[$internalFunctionName][$countCalled];
        static::$callCounters[$internalFunctionName] = $countCalled + 1;

        $ret = $args === null
            ? $callback()
            : $callback(...$args);

        return $ret;
    }


    /**
     * Add null callback if it is not already there
     *
     * @param string $internalFunctionName
     */
    protected static function addNullCallback(string $internalFunctionName): void
    {
        static::$registeredCallBacks[$internalFunctionName] = static::$registeredCallBacks[$internalFunctionName] ?? [];
        if (!\in_array(null, static::$registeredCallBacks[$internalFunctionName], true)) {
            static::$registeredCallBacks[$internalFunctionName][] = null;
        }
    }

    protected static function register(string $internalFunctionName, string $class): void
    {
        $self     = \get_called_class();
        $mockedNs = [\substr($class, 0, \strrpos($class, '\\'))];
        if (0 < strpos($class, '\\Tests\\')) {
            $ns         = \str_replace('\\Tests\\', '\\', $class);
            $mockedNs[] = \substr($ns, 0, \strrpos($ns, '\\'));
        }
        elseif (0 === \strpos($class, 'Tests\\')) {
            $mockedNs[] = \substr($class, 6, \strrpos($class, '\\') - 6);
        }
        foreach ($mockedNs as $ns) {
            if (\function_exists($ns . '\\' . $internalFunctionName)) {
                continue;
            }
            eval(
            <<<EOPHP
namespace $ns;

function $internalFunctionName(...\$args)
{
   return \\$self::call('$internalFunctionName', ...\$args);
}

EOPHP
            );
        }
    }
}
