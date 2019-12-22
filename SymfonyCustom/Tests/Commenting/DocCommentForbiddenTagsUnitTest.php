<?php

namespace SymfonyCustom\Tests\Commenting;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the DocCommentForbiddenTags sniff.
 *
 * @group SymfonyCustom
 */
class DocCommentForbiddenTagsUnitTest extends AbstractSniffUnitTest
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
            6  => 1,
            7  => 1,
            11 => 1,
            15 => 1,
            20 => 1,
            21 => 1,
        ];
    }

    /**
     * Returns the lines where warnings should occur.
     *
     * The key of the array should represent the line number and the value
     * should represent the number of warnings that should occur on that line.
     *
     * @return array(int => int)
     */
    protected function getWarningList(): array
    {
        return [];
    }
}
