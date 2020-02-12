<?php

declare(strict_types=1);

namespace TwigCS\Tests\Ruleset\Generic\DelimiterSpacing;

use TwigCS\Ruleset\Generic\DelimiterSpacingSniff;
use TwigCS\Tests\Ruleset\AbstractSniffTest;

/**
 * Class DelimiterSpacingTest
 */
class DelimiterSpacingTest extends AbstractSniffTest
{
    public function testSniff(): void
    {
        $this->checkSniff(new DelimiterSpacingSniff(), [
            [12 => 1],
            [12 => 12],
            [12 => 15],
            [12 => 25],
        ]);
    }
}
