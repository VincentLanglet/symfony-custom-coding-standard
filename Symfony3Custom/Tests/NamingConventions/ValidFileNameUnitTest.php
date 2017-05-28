<?php

namespace Symfony3Custom\Tests\NamingConventions;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the ValidFileName sniff.
 *
 * @group Symfony3Custom
 */
class ValidFileNameUnitTest extends AbstractSniffUnitTest
{
    /**
     * Returns the lines where errors should occur.
     *
     * The key of the array should represent the line number and the value
     * should represent the number of errors that should occur on that line.
     *
     * @param string $filename
     *
     * @return array<int, int>
     */
    public function getErrorList($filename = '')
    {
        switch ($filename) {
            case 'ValidFileNameUnitTest.inc':
                return [];
            case 'ValidFileNameUnitTest.Invalid.inc':
                return [
                    1 => 1,
                ];
            default:
                return [];
        }
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
