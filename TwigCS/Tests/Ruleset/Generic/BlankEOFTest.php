<?php

declare(strict_types=1);

namespace TwigCS\Tests\Ruleset\Generic;

use TwigCS\Ruleset\Generic\BlankEOFSniff;
use TwigCS\Tests\AbstractSniffTest;

/**
 * Class BlankEOFTest
 */
class BlankEOFTest extends AbstractSniffTest
{
    public function testSniff(): void
    {
        $this->checkGenericSniff(new BlankEOFSniff(), [
            [4 => 1],
        ]);
    }
}
