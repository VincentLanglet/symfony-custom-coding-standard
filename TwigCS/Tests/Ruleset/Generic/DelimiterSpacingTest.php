<?php

namespace TwigCS\Tests\Ruleset\Generic;

use TwigCS\Ruleset\Generic\DelimiterSpacingSniff;
use TwigCS\Tests\AbstractSniffTest;

/**
 * Class DelimiterSpacingTest
 */
class DelimiterSpacingTest extends AbstractSniffTest
{
    public function testSniff(): void
    {
        $this->checkGenericSniff(new DelimiterSpacingSniff(), [
            [12 => 1],
            [12 => 12],
            [12 => 15],
            [12 => 25],
        ]);
    }
}
