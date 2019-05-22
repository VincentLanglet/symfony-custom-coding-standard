<?php

namespace TwigCS\Tests\Sniff;

use TwigCS\Sniff\Standard\EnsureBlankAtEOFSniff;
use TwigCS\Tests\AbstractSniffTest;

/**
 * Class EnsureBlankAtEOFTest
 */
class EnsureBlankAtEOFTest extends AbstractSniffTest
{
    public function testSniff1()
    {
        $this->checkGenericSniff('ensureBlankAtEOF.twig', new EnsureBlankAtEOFSniff(), [
            [4, 1],
        ]);
    }
}
