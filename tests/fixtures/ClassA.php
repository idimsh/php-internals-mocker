<?php
declare(strict_types=1);

namespace idimsh\PhpInternalsMockerTest\fixtures\Ns1;

class ClassA
{
    public function callUnlink($fileName)
    {
        return unlink($fileName);
    }

    public function returnTrue()
    {
        return true;
    }
}
