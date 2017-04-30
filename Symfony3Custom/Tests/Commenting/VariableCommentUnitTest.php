<?php

/**
 * Unit test class for VariableCommentSniff.
 */
class Symfony3Custom_Tests_Commenting_VariableCommentUnitTest extends AbstractSniffUnitTest
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
                21  => 1,
                24  => 1,
                56  => 1,
                64  => 1,
                73  => 1,
                84  => 1,
                130 => 1,
                132 => 1,
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
        return array(93 => 1);
    }
}
