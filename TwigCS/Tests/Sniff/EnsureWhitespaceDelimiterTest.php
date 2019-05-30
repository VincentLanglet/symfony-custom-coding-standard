<?php

namespace TwigCS\Tests\Sniff;

use TwigCS\Sniff\Standard\EnsureWhitespaceDelimiterSniff;
use TwigCS\Tests\AbstractSniffTest;

/**
 * Class EnsureWhitespaceDelimiterTest
 */
class EnsureWhitespaceDelimiterTest extends AbstractSniffTest
{
    public function testSniff1()
    {
        $this->checkGenericSniff('ensureWhitespaceDelimiter.twig', new EnsureWhitespaceDelimiterSniff(), [
            [12, 1],
            [12, 12],
            [12, 15],
            [12, 25],
        ]);
    }
}
