<?php

namespace SymfonyCustom\Tests\WhiteSpace;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the CloseBracketSpacing sniff.
 *
 * @group SymfonyCustom
 */
class DocCommentTagSpacingUnitTest extends AbstractSniffUnitTest
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
            11 => 1,
            13 => 1,
            14 => 1,
            21 => 1,
            22 => 1,
            24 => 1,
            28 => 1,
        );
    }

    /**
     * Returns the lines where warnings should occur.
     *
     * The key of the array should represent the line number and the value
     * should represent the number of warnings that should occur on that line.
     *
     * @return array(int => int)
     */
    protected function getWarningList()
    {
        return array();
    }
}
