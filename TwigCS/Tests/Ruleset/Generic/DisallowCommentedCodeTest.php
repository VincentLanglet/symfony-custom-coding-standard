<?php

namespace TwigCS\Tests\Ruleset\Generic;

use TwigCS\Ruleset\Generic\DisallowCommentedCodeSniff;
use TwigCS\Tests\AbstractSniffTest;

/**
 * Class DisallowCommentedCodeTest
 */
class DisallowCommentedCodeTest extends AbstractSniffTest
{
    public function testSniff()
    {
        $this->checkGenericSniff(new DisallowCommentedCodeSniff(), [
            [2, 5],
        ]);
    }
}
