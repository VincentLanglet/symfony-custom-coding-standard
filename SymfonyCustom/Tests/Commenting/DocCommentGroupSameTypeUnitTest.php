<?php

declare(strict_types=1);

namespace SymfonyCustom\Tests\Commenting;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the DocCommentGroupSameType sniff.
 *
 * @group SymfonyCustom
 */
class DocCommentGroupSameTypeUnitTest extends AbstractSniffUnitTest
{
    /**
     * @return array
     */
    protected function getErrorList(): array
    {
        return [
            5  => 1,
            7  => 1,
            8  => 1,
            10 => 1,
            13 => 1,
            14 => 1,
            15 => 1,
            20 => 1,
            29 => 1,
            33 => 1,
            67 => 1,
            75 => 1,
        ];
    }

    /**
     * @return array
     */
    protected function getWarningList(): array
    {
        return [];
    }
}
