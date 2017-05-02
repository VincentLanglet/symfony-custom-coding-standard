<?php

/**
 * Unit test class for the YodaConditions sniff.
 *
 * A sniff unit test checks a .inc file for expected violations of a single
 * coding standard. Expected errors and warnings are stored in this class.
 */
class Symfony3Custom_Tests_Formatting_YodaConditionUnitTest extends AbstractSniffUnitTest
{
    /**
     * Returns the lines where errors should occur.
     *
     * @return array <int line number> => <int number of errors>
     */
    public function getErrorList()
    {
        return array(
            2  => 2,
            4  => 2,
            11 => 1,
            18 => 1,
            25 => 1,
            32 => 1,
            49 => 1,
            55 => 1,
            62 => 1,
            68 => 1,
            87 => 1,
            92 => 1,
            95 => 1,
        );
    }

    /**
     * Returns the lines where warnings should occur.
     *
     * @return array <int line number> => <int number of warnings>
     */
    public function getWarningList()
    {
        return array();
    }
}