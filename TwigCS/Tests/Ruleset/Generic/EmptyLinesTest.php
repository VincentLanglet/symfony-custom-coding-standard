<?php

namespace TwigCS\Tests\Ruleset\Generic;

use TwigCS\Ruleset\Generic\EmptyLinesSniff;
use TwigCS\Tests\AbstractSniffTest;

/**
 * Class EmptyLinesTest
 */
class EmptyLinesTest extends AbstractSniffTest
{
    public function testSniff()
    {
        $this->checkGenericSniff(new EmptyLinesSniff(), [
            [3 => 1],
        ]);
    }
}
