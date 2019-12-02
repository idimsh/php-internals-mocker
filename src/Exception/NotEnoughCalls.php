<?php
declare(strict_types=1);

namespace idimsh\PhpInternalsMocker\Exception;

class NotEnoughCalls extends Exception
{
    public static function fromFunctionName(
        string $internalFunctionName,
        int $expectedInvocationCount,
        int $invocationCount
    ): self
    {
        $message = \sprintf(
            'Function [%s] was expected to be called %d times, actually called %d times. In:%s',
            $internalFunctionName,
            $expectedInvocationCount,
            $invocationCount,
            "\n" . self::getCustomTrace()
        );

        return new static($message);
    }
}
