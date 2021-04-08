<?php

declare(strict_types=1);

namespace SymfonyCustom\Tests\WhiteSpace;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the DocCommentTagSpacing sniff.
 */
class DocCommentTagSpacingUnitTest extends AbstractSniffUnitTest
{
    /**
     * @return array<int, int>
     */
    protected function getErrorList(): array
    {
        return [
            11 => 1,
            13 => 1,
            14 => 1,
            39 => 1,
            40 => 1,
            42 => 1,
            46 => 1,
        ];
    }

    /**
     * @return array<int, int>
     */
    protected function getWarningList(): array
    {
        return [];
    }
}
