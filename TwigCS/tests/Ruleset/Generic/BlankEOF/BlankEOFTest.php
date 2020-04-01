<?php

declare(strict_types=1);

namespace TwigCS\Tests\Ruleset\Generic\BlankEOF;

use TwigCS\Ruleset\Generic\BlankEOFSniff;
use TwigCS\Tests\Ruleset\AbstractSniffTest;

/**
 * Class BlankEOFTest
 */
class BlankEOFTest extends AbstractSniffTest
{
    /**
     * @return void
     */
    public function testSniff(): void
    {
        $this->checkSniff(new BlankEOFSniff(), [
            [4 => 1],
        ]);
    }
}
