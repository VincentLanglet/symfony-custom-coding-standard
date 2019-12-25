<?php

declare(strict_types=1);

namespace TwigCS\Tests\Ruleset\Generic;

use TwigCS\Ruleset\Generic\EmptyLinesSniff;
use TwigCS\Tests\AbstractSniffTest;

/**
 * Class EmptyLinesTest
 */
class EmptyLinesTest extends AbstractSniffTest
{
    public function testSniff(): void
    {
        $this->checkGenericSniff(new EmptyLinesSniff(), [
            [3 => 1],
        ]);
    }
}
