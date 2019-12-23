<?php

declare(strict_types=1);

namespace SymfonyCustom\Tests\Classes;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the ClassDeclaration sniff.
 *
 * @group SymfonyCustom
 */
class ClassDeclarationUnitTest extends AbstractSniffUnitTest
{
    /**
     * @return array
     */
    protected function getErrorList(): array
    {
        return [
            4  => 1,
            13 => 1,
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
