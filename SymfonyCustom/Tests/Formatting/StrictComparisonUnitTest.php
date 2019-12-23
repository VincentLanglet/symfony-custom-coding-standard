<?php

declare(strict_types=1);

namespace SymfonyCustom\Tests\Formatting;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the StrictComparison sniff.
 *
 * @group SymfonyCustom
 */
class StrictComparisonUnitTest extends AbstractSniffUnitTest
{
    /**
     * @return array
     */
    protected function getErrorList(): array
    {
        return [
            3 => 1,
            5 => 1,
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
