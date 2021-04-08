<?php

declare(strict_types=1);

namespace SymfonyCustom\Tests\Commenting;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the FunctionComment sniff.
 */
class FunctionCommentUnitTest extends AbstractSniffUnitTest
{
    /**
     * @return array<int, int>
     */
    protected function getErrorList(): array
    {
        return [
            5   => 1,
            10  => 1,
            31  => 1,
            41  => 1,
            47  => 1,
            52  => 1,
            82  => 2,
            89  => 1,
            95  => 1,
            124 => 1,
            133 => 1,
            141 => 1,
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
