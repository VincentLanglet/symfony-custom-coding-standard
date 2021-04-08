<?php

declare(strict_types=1);

namespace SymfonyCustom\Tests\Errors;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the ExceptionMessage sniff.
 */
class ExceptionMessageUnitTest extends AbstractSniffUnitTest
{
    /**
     * @return array<int, int>
     */
    protected function getErrorList(): array
    {
        return [
            17 => 1,
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
