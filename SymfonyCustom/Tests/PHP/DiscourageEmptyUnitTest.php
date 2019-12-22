<?php

namespace SymfonyCustom\Tests\Objects;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the DiscourageEmpty sniff.
 *
 * @group SymfonyCustom
 */
class DiscourageEmptyUnitTest extends AbstractSniffUnitTest
{
    /**
     * @return array
     */
    protected function getErrorList(): array
    {
        return [];
    }

    /**
     * @return array
     */
    protected function getWarningList(): array
    {
        return [
            3 => 1,
        ];
    }
}
