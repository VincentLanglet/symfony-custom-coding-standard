<?php

declare(strict_types=1);

namespace SymfonyCustom\Tests\Classes;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the PropertyDeclaration sniff.
 */
class PropertyDeclarationUnitTest extends AbstractSniffUnitTest
{
    /**
     * @return array<int, int>
     */
    protected function getErrorList(): array
    {
        return [
            9  => 1,
            43 => 1,
            54 => 1,
            61 => 1,
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
