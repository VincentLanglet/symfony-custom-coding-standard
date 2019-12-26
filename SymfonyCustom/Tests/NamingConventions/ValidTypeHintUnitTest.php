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
            26 => 1,
            27 => 1,
            28 => 1,
            29 => 1,
            30 => 1,
            32 => 1,
            33 => 1,
            34 => 1,
            35 => 1,
            36 => 1,
            39 => 1,
            40 => 1,
            41 => 1,
            42 => 1,
            43 => 1,
            45 => 1,
            46 => 1,
            47 => 1,
            50 => 1,
            51 => 1,
            52 => 1,
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
