<?php
declare(strict_types=1);

namespace idimsh\PhpInternalsMockerTest;

use idimsh\PhpInternalsMocker\Exception\NotEnoughCalls;
use idimsh\PhpInternalsMocker\PhpFunctionSimpleMocker;

/**
 * @covers \idimsh\PhpInternalsMocker\PhpFunctionSimpleMocker
 */
class PhpFunctionSimpleMocker1Test extends \PHPUnit\Framework\TestCase
{
    public const FIXTURES_DIR = __DIR__ . '/fixtures';

    /**
     * @var  \idimsh\PhpInternalsMockerTest\fixtures\Ns1\ClassA
     */
    protected $classAInstance;

    protected function setUp(): void
    {
        parent::setUp();
        PhpFunctionSimpleMocker::reset();

        require_once __DIR__ . '/fixtures/ClassA.php';

        $this->classAInstance = new \idimsh\PhpInternalsMockerTest\fixtures\Ns1\ClassA();
    }

    public function test_add_WithOneCallCalledOnce(): void
    {
        $callingFileName = \uniqid('callingFileName-');
        $callingReturn   = false;

        PhpFunctionSimpleMocker::add(
            'unlink',
            \idimsh\PhpInternalsMockerTest\fixtures\Ns1\ClassA::class,
            function ($fileName) use ($callingFileName, $callingReturn) {
                static::assertSame($fileName, $callingFileName);
                return $callingReturn;
            }
        );

        $actual = $this->classAInstance->callUnlink($callingFileName);
        static::assertSame($callingReturn, $actual);

        /** @noinspection PhpUnhandledExceptionInspection */
        PhpFunctionSimpleMocker::phpUnitAssertNotEnoughCalls($this);
    }

    public function test_add_WithOneCallCalledMultiple(): void
    {
        $callingFileName = \uniqid('callingFileName-');
        $callingReturn   = false;

        PhpFunctionSimpleMocker::add(
            'unlink',
            \idimsh\PhpInternalsMockerTest\fixtures\Ns1\ClassA::class,
            function ($fileName) use ($callingFileName, $callingReturn) {
                static::assertSame($fileName, $callingFileName);
                return $callingReturn;
            }
        );

        $actual = $this->classAInstance->callUnlink($callingFileName);
        static::assertSame($callingReturn, $actual);

        static::expectException(\idimsh\PhpInternalsMocker\Exception\CallsLimitExceeded::class);
        $this->classAInstance->callUnlink($callingFileName);

        /** @noinspection PhpUnhandledExceptionInspection */
        PhpFunctionSimpleMocker::phpUnitAssertNotEnoughCalls($this);
    }

    public function test_add_WithMultiCallsCalledMultiple(): void
    {
        $callingFileName1 = \uniqid('callingFileName-1-');
        $callingFileName2 = \uniqid('callingFileName-2-');
        $callingFileName3 = \uniqid('callingFileName-3-');
        $callingReturn1   = false;
        $callingReturn2   = true;
        $callingReturn3   = false;

        PhpFunctionSimpleMocker::add(
            'unlink',
            \idimsh\PhpInternalsMockerTest\fixtures\Ns1\ClassA::class,
            function ($fileName) use (
                $callingFileName1,
                $callingFileName2,
                $callingFileName3,
                $callingReturn1,
                $callingReturn2,
                $callingReturn3
            ) {
                static $callCount = 0;
                $callCount++;
                switch ($callCount) {
                    case 1:
                        static::assertSame($fileName, $callingFileName1);
                        return $callingReturn1;
                    case 2:
                        static::assertSame($fileName, $callingFileName2);
                        return $callingReturn2;
                    case 3:
                        static::assertSame($fileName, $callingFileName3);
                        return $callingReturn3;
                    default:
                        throw new \Exception('We should not reach here');
                }
            },
            3
        );

        $actual = $this->classAInstance->callUnlink($callingFileName1);
        static::assertSame($callingReturn1, $actual);

        $actual = $this->classAInstance->callUnlink($callingFileName2);
        static::assertSame($callingReturn2, $actual);

        $actual = $this->classAInstance->callUnlink($callingFileName3);
        static::assertSame($callingReturn3, $actual);

        /** @noinspection PhpUnhandledExceptionInspection */
        PhpFunctionSimpleMocker::phpUnitAssertNotEnoughCalls($this);
    }

    public function test_add_WithNeverExpected(): void
    {
        $callingFileName = \uniqid('callingFileName-');

        PhpFunctionSimpleMocker::add(
            'unlink',
            \idimsh\PhpInternalsMockerTest\fixtures\Ns1\ClassA::class,
            null
        );

        static::expectException(\idimsh\PhpInternalsMocker\Exception\NeverExpected::class);
        $this->classAInstance->callUnlink($callingFileName);

        /** @noinspection PhpUnhandledExceptionInspection */
        PhpFunctionSimpleMocker::phpUnitAssertNotEnoughCalls($this);
    }

    public function test_add_NotCalled(): void
    {
        $callingFileName = \uniqid('callingFileName-');
        $callingReturn   = false;

        PhpFunctionSimpleMocker::add(
            'unlink',
            \idimsh\PhpInternalsMockerTest\fixtures\Ns1\ClassA::class,
            function ($fileName) use ($callingFileName, $callingReturn) {
                static::assertSame($fileName, $callingFileName);
                return $callingReturn;
            }
        );

        static::assertTrue($this->classAInstance->returnTrue());

        /**
         * Here we will not call
         * PhpFunctionSimpleMocker::phpUnitAssertNotEnoughCalls($this);
         *
         * But will expect the exception resulted from a call to
         * PhpFunctionSimpleMocker::assertPostConditions()
         *
         * Since that guarantees failure
         */
        static::expectException(NotEnoughCalls::class);
        static::expectExceptionMessageRegExp('#Function [^ ]+ was expected to be called \d+ times?, actually called \d+ times?#');

        /** @noinspection PhpUnhandledExceptionInspection */
        PhpFunctionSimpleMocker::assertPostConditions();
    }
}
