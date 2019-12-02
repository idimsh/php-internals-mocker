<?php
declare(strict_types=1);

namespace idimsh\PhpInternalsMockerTest;

/**
 * @covers \idimsh\PhpInternalsMocker\PhpFunctionSimpleMocker
 */
class PhpFunctionSimpleMockerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test that true does in fact equal true
     */
    public function testTrueIsTrue()
    {
        $this->assertTrue(true);
    }
}
