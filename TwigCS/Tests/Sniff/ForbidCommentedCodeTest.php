<?php

namespace TwigCS\Tests\Sniff;

use TwigCS\Sniff\Standard\ForbidCommentedCodeSniff;
use TwigCS\Tests\AbstractSniffTest;

/**
 * Class ForbidCommentedCodeTest
 */
class ForbidCommentedCodeTest extends AbstractSniffTest
{
    public function testSniff1()
    {
        $this->checkGenericSniff('forbidCommentedCode.twig', new ForbidCommentedCodeSniff(), [
            [2, 5],
        ]);
    }
}
