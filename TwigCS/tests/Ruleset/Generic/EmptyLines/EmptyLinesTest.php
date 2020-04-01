<?php

declare(strict_types=1);

namespace TwigCS\Tests\Ruleset\Generic\EmptyLines;

use TwigCS\Ruleset\Generic\EmptyLinesSniff;
use TwigCS\Tests\Ruleset\AbstractSniffTest;

/**
 * Class EmptyLinesTest
 */
class EmptyLinesTest extends AbstractSniffTest
{
    /**
     * @return void
     */
    public function testSniff(): void
    {
        $this->checkSniff(new EmptyLinesSniff(), [
            [3 => 1],
        ]);
    }
}
