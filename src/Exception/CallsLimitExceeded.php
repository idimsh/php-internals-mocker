<?php
declare(strict_types=1);

namespace idimsh\PhpInternalsMocker\Exception;

class CallsLimitExceeded extends Exception
{
    public static function fromFunctionNameLimit(string $internalFunctionName, int $limit): self
    {
        $message = \sprintf(
            'Function %s is not expected to be called more than %d %s. In:%s',
            $internalFunctionName,
            $limit,
            $limit > 1 ? 'times' : 'time',
            "\n" . self::getCustomTrace()
        );

        return new static($message);
    }
}
