<?php

declare(strict_types=1);

namespace SymfonyCustom\Tests\WhiteSpace;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the OpenBracketSpacing sniff.
 *
 * @group SymfonyCustom
 */
class OpenBracketSpacingUnitTest extends AbstractSniffUnitTest
{
    /**
     * @return array
     */
    protected function getErrorList(): array
    {
        return [
            3 => 1,
            5 => 2,
            7 => 1,
            9 => 1,
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
