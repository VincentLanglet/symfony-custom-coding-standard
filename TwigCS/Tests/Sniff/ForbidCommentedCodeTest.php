<?php

namespace TwigCS\Tests\Sniff;

use TwigCS\Sniff\Standard\ForbidCommentedCodeSniff;
use TwigCS\Tests\AbstractSniffTest;

/**
 * Class ForbidCommentedCodeTest
 */
class ForbidCommentedCodeTest extends AbstractSniffTest
{
    public function testSniff()
    {
        $this->checkGenericSniff(new ForbidCommentedCodeSniff(), [
            [2, 5],
        ]);
    }
}
