<?php

/**
 * Unit test class for the DocCommentGroupSameType sniff.
 *
 * A sniff unit test checks a .inc file for expected violations of a single
 * coding standard. Expected errors and warnings are stored in this class.
 */
class Symfony3Custom_Tests_Commenting_DocCommentGroupSameTypeUnitTest extends AbstractSniffUnitTest
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
            5 => 1,
            7 => 1,
            8 => 1,
            10 => 1,
            13 => 1,
            14 => 1,
            15 => 1,
            20 => 1,
            29 => 1,
            33 => 1,
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
