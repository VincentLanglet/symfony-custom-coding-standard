<?php

declare(strict_types=1);

namespace SymfonyCustom\Tests\PHP;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the DiscourageEmpty sniff.
 */
class DiscourageEmptyUnitTest extends AbstractSniffUnitTest
{
    /**
     * @return array<int, int>
     */
    protected function getErrorList(): array
    {
        return [];
    }

    /**
     * @return array<int, int>
     */
    protected function getWarningList(): array
    {
        return [
            3 => 1,
        ];
    }
}
