<?php

declare(strict_types=1);

namespace SymfonyCustom\Tests\Commenting;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the DocCommentForbiddenTags sniff.
 */
class DocCommentForbiddenTagsUnitTest extends AbstractSniffUnitTest
{
    /**
     * @return array
     */
    protected function getErrorList(): array
    {
        return [
            6  => 1,
            7  => 1,
            11 => 1,
            15 => 1,
            20 => 1,
            21 => 1,
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
