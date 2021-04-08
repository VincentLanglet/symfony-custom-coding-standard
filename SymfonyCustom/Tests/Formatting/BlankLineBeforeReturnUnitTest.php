<?php

declare(strict_types=1);

namespace SymfonyCustom\Tests\Formatting;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the BlankLineBeforeReturn sniff.
 */
class BlankLineBeforeReturnUnitTest extends AbstractSniffUnitTest
{
    /**
     * @return array<int, int>
     */
    protected function getErrorList(): array
    {
        return [
            60 => 1,
            67 => 1,
            76 => 1,
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
