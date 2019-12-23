<?php

declare(strict_types=1);

namespace SymfonyCustom\Tests\NamingConventions;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the ValidClassName sniff.
 *
 * @group SymfonyCustom
 */
class ValidClassNameUnitTest extends AbstractSniffUnitTest
{
    /**
     * @return array
     */
    protected function getErrorList(): array
    {
        return [
            3  => 1,
            11 => 1,
            19 => 1,
            23 => 1,
            31 => 1,
            35 => 1,
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
