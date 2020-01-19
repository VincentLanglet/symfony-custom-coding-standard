<?php

declare(strict_types=1);

namespace SymfonyCustom\Tests\Namespaces;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the UnusedUse sniff.
 */
class UnusedUseUnitTest extends AbstractSniffUnitTest
{
    /**
     * @return array
     */
    protected function getErrorList(): array
    {
        return [
            12  => 1,
            13  => 1,
            14  => 1,
            15  => 2,
            16  => 2,
            18  => 1,
            20  => 1,
            21  => 1,
            22  => 1,
            23  => 1,
            93  => 1,
            95  => 1,
            124 => 1,
            125 => 1,
            126 => 1,
            127 => 1,
            128 => 1,
            129 => 1,
        ];
    }

    /**
     * @return array
     */
    protected function getWarningList(): array
    {
        return [];
    }
}
