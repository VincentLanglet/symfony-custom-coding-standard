<?php

declare(strict_types=1);

namespace SymfonyCustom\Tests\Objects;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the ObjectInstantiation sniff.
 *
 * @group SymfonyCustom
 */
class ObjectInstantiationUnitTest extends AbstractSniffUnitTest
{
    /**
     * @return array
     */
    protected function getErrorList(): array
    {
        return [
            4  => 1,
            5  => 1,
            6  => 1,
            7  => 1,
            8  => 1,
            9  => 1,
            10 => 1,
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
