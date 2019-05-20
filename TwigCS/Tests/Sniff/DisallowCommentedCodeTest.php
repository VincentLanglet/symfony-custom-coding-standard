<?php

namespace TwigCS\Tests\Sniff;

use TwigCS\Sniff\Standard\DisallowCommentedCodeSniff;
use TwigCS\Tests\AbstractSniffTest;

/**
 * Class DisallowCommentedCodeTest
 */
class DisallowCommentedCodeTest extends AbstractSniffTest
{
    public function testSniff1()
    {
        $this->checkGenericSniff('disallowCommentedCode.twig', new DisallowCommentedCodeSniff(), [
            [2, 5],
        ]);
    }
}
