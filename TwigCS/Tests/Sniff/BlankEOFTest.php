<?php

namespace TwigCS\Tests\Sniff;

use TwigCS\Sniff\Standard\BlankEOFSniff;
use TwigCS\Tests\AbstractSniffTest;

/**
 * Class BlankEOFTest
 */
class BlankEOFTest extends AbstractSniffTest
{
    public function testSniff()
    {
        $this->checkGenericSniff(new BlankEOFSniff(), [
            [4, 1],
        ]);
    }
}
