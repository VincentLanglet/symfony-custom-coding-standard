<?php

declare(strict_types=1);

namespace SymfonyCustom\Tests\WhiteSpace;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the UnaryOperatorSpacing sniff.
 *
 * @group SymfonyCustom
 */
class UnaryOperatorSpacingUnitTest extends AbstractSniffUnitTest
{
    /**
     * @return array
     */
    protected function getErrorList(): array
    {
        return [
            4  => 1,
            7  => 1,
            9  => 1,
            12 => 1,
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
