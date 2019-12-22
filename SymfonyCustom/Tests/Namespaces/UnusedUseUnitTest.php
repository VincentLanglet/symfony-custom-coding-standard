<?php

namespace SymfonyCustom\Tests\Namespaces;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the UnusedUse sniff.
 *
 * @group SymfonyCustom
 */
class UnusedUseUnitTest extends AbstractSniffUnitTest
{
    /**
     * Returns the lines where errors should occur.
     *
     * The key of the array should represent the line number and the value
     * should represent the number of errors that should occur on that line.
     *
     * @return array<int, int>
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
     * Returns the lines where warnings should occur.
     *
     * The key of the array should represent the line number and the value
     * should represent the number of errors that should occur on that line.
     *
     * @return array(int => int)
     */
    protected function getWarningList(): array
    {
        return [];
    }
}
