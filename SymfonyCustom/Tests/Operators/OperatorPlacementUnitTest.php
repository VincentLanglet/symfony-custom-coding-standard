<?php

declare(strict_types=1);

namespace SymfonyCustom\Tests\Operators;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the OperatorPlacement sniff.
 */
class OperatorPlacementUnitTest extends AbstractSniffUnitTest
{
    /**
     * @return array<int, int>
     */
    protected function getErrorList(): array
    {
        return [
            3  => 1,
            6  => 1,
            12 => 1,
            13 => 1,
            16 => 1,
            19 => 1,
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
