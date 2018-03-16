<?php

namespace SymfonyCustom\Tests\Arrays;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the ArrayDeclaration sniff.
 *
 * @group SymfonyCustom
 */
class ArrayDeclarationUnitTest extends AbstractSniffUnitTest
{
    /**
     * Returns the lines where errors should occur.
     *
     * The key of the array should represent the line number and the value
     * should represent the number of errors that should occur on that line.
     *
     * @return array<int, int>
     */
    public function getErrorList()
    {
        return array(
            7  => 2,
            9  => 1,
            22 => 1,
            23 => 1,
            24 => 1,
            25 => 1,
            31 => 1,
            35 => 1,
            36 => 1,
            41 => 1,
            46 => 1,
            47 => 1,
            50 => 1,
            51 => 1,
            53 => 1,
            58 => 1,
            61 => 1,
            62 => 1,
            63 => 1,
            64 => 1,
            65 => 1,
            66 => 2,
            67 => 1,
            68 => 1,
            70 => 1,
        );
    }

    /**
     * Returns the lines where warnings should occur.
     *
     * The key of the array should represent the line number and the value
     * should represent the number of warnings that should occur on that line.
     *
     * @return array<int, int>
     */
    public function getWarningList()
    {
        return array();
    }
}
