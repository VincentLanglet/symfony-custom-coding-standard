<?php

declare(strict_types=1);

namespace SymfonyCustom\Tests\PHP;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the EncourageMultiBytes sniff.
 */
class EncourageMultiBytesUnitTest extends AbstractSniffUnitTest
{
    /**
     * @return array<int, int>
     */
    protected function getErrorList(): array
    {
        return [];
    }

    /**
     * @return array<int, int>
     */
    protected function getWarningList(): array
    {
        return [
            3  => 1,
            4  => 1,
            5  => 1,
            6  => 1,
            7  => 1,
            8  => 1,
            9  => 1,
            10 => 1,
            11 => 1,
            12 => 1,
            13 => 1,
            14 => 1,
            15 => 1,
        ];
    }
}
