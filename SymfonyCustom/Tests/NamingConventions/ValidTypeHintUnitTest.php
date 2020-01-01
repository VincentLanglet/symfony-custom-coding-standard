<?php

declare(strict_types=1);

namespace SymfonyCustom\Tests\NamingConventions;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the ValidScalarTypeName sniff.
 *
 * @group SymfonyCustom
 */
class ValidTypeHintUnitTest extends AbstractSniffUnitTest
{
    /**
     * @return array
     */
    protected function getErrorList(): array
    {
        return [
            5  => 1,
            10 => 1,
            11 => 1,
            12 => 1,
            13 => 1,
            19 => 1,
            20 => 1,
            26 => 1,
            27 => 1,
            28 => 1,
            29 => 1,
            30 => 1,
            36 => 1,
            37 => 1,
            38 => 1,
            39 => 1,
            45 => 1,
            46 => 1,
            47 => 1,
            48 => 1,
            49 => 1,
            50 => 1,
            51 => 1,
            57 => 1,
            58 => 1,
            64 => 1,
            65 => 1,
            71 => 1,
            72 => 1,
            73 => 1,
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
