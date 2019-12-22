<?php

namespace SymfonyCustom\Tests\Formatting;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the YodaConditions sniff.
 *
 * @group SymfonyCustom
 */
class YodaConditionUnitTest extends AbstractSniffUnitTest
{
    /**
     * @return array
     */
    protected function getErrorList(): array
    {
        return [
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
