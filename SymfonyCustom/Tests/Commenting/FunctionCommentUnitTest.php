<?php

declare(strict_types=1);

namespace SymfonyCustom\Tests\Commenting;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the FunctionComment sniff.
 *
 * @group SymfonyCustom
 */
class FunctionCommentUnitTest extends AbstractSniffUnitTest
{
    /**
     * @return array
     */
    protected function getErrorList(): array
    {
        return [
            5   => 1,
            10  => 1,
            43  => 1,
            48  => 1,
            76  => 2,
            83  => 1,
            125 => 1,
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
