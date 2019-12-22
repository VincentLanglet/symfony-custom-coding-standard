<?php

namespace SymfonyCustom\Tests\Arrays;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the ArrayDeclaration sniff.
 *
 * @group SymfonyCustom
 */
class ArrayDeclarationUnitTest extends AbstractSniffUnitTest
{
    /**
     * @return array
     */
    protected function getErrorList(): array
    {
        return [
            7   => 2,
            9   => 1,
            22  => 1,
            23  => 1,
            24  => 2,
            25  => 1,
            31  => 1,
            35  => 1,
            36  => 1,
            41  => 1,
            46  => 1,
            47  => 1,
            50  => 1,
            51  => 1,
            53  => 1,
            58  => 1,
            61  => 1,
            62  => 1,
            63  => 1,
            64  => 1,
            65  => 1,
            66  => 2,
            67  => 1,
            68  => 1,
            70  => 1,
            77  => 1,
            86  => 1,
            93  => 3,
            95  => 3,
            100 => 3,
            102 => 4,
            107 => 2,
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
