<?php

declare(strict_types=1);

namespace SymfonyCustom\Tests\Formatting;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the ConditionalReturnOrThrow sniff.
 */
class ConditionalReturnOrThrowUnitTest extends AbstractSniffUnitTest
{
    /**
     * @return array<int, int>
     */
    protected function getErrorList(): array
    {
        return [
            22 => 1,
            37 => 1,
            44 => 1,
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
