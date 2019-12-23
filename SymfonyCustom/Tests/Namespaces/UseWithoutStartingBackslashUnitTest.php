<?php

declare(strict_types=1);

namespace SymfonyCustom\Tests\Namespaces;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the UseWithoutStartingBackslash sniff.
 *
 * @group SymfonyCustom
 */
class UseWithoutStartingBackslashUnitTest extends AbstractSniffUnitTest
{
    /**
     * @return array
     */
    protected function getErrorList(): array
    {
        return [
            5 => 1,
            6 => 1,
            7 => 1,
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
