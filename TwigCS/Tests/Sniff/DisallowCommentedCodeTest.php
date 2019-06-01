<?php

namespace TwigCS\Tests\Sniff;

use TwigCS\Sniff\Standard\DisallowCommentedCodeSniff;
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
