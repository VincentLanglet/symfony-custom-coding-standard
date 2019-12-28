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
            7  => 1,
            8  => 1,
            9  => 1,
            10 => 1,
            11 => 1,
            13 => 1,
            14 => 1,
            15 => 1,
            16 => 1,
            17 => 1,
            20 => 1,
            21 => 1,
            22 => 1,
            23 => 1,
            24 => 1,
            26 => 1,
            27 => 1,
            28 => 1,
            31 => 1,
            32 => 1,
            33 => 1,
            36 => 1,
            37 => 1,
            38 => 1,
            39 => 1,
            40 => 1,
            43 => 1,
            44 => 1,
            45 => 1,
            49 => 1,
            50 => 1,
            51 => 1,
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
