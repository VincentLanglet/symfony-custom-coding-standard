<?php

declare(strict_types=1);

namespace SymfonyCustom\Sniffs\Functions;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the ScopeOrder sniff.
 */
class ScopeOrderUnitTest extends AbstractSniffUnitTest
{
    /**
     * @return array<int, int>
     */
    protected function getErrorList(): array
    {
        return [
            9  => 1,
            20 => 1,
            31 => 1,
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
