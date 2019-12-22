<?php

namespace SymfonyCustom\Tests\Namespaces;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the UseWithoutStartingBackslash sniff.
 *
 * @group SymfonyCustom
 */
class UseWithoutStartingBackslashUnitTest extends AbstractSniffUnitTest
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
        return [
            2 => 1,
            3 => 1,
            4 => 1,
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
    public function getWarningList()
    {
        return [];
    }
}
