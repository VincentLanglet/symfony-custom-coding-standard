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
    /**
     * @return void
     */
    public function testSniff(): void
    {
        $this->checkSniff(new DelimiterSpacingSniff(), [
            [15 => 1],
            [15 => 12],
            [15 => 15],
            [15 => 25],
        ]);
    }
}
