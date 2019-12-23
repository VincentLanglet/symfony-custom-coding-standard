<?php

declare(strict_types=1);

namespace SymfonyCustom\Tests\Commenting;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the DocComment sniff.
 *
 * @group SymfonyCustom
 */
class DocCommentUnitTest extends AbstractSniffUnitTest
{
    /**
     * @return array
     */
    protected function getErrorList(): array
    {
        return [
            9  => 1,
            13 => 1,
            16 => 1,
            18 => 1,
            26 => 1,
            28 => 1,
            34 => 1,
            36 => 1,
            38 => 1,
            40 => 1,
            42 => 1,
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
