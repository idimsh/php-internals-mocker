<?php
declare(strict_types=1);

namespace idimsh\PhpInternalsMocker\Exception;

class NeverExpected extends Exception
{
    public static function fromFunctionName(string $internalFunctionName): self
    {
        $message = \sprintf(
            'Function %s was never expected to be called. In:%s',
            $internalFunctionName,
            "\n" . self::getCustomTrace()
        );
        return new static($message);
    }
}
