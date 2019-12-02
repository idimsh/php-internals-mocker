<?php
declare(strict_types=1);

namespace idimsh\PhpInternalsMocker\Exception;

class Exception extends \Exception
{
    protected static function getCustomTrace(): string
    {
        $trace = (new \Exception())->getTraceAsString();
        $trace = \explode("\n", $trace);
        \array_shift($trace);
        \array_shift($trace);
        return \implode("\n", $trace);
    }
}
