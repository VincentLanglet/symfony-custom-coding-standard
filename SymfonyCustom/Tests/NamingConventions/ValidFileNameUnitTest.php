<?php

declare(strict_types=1);

namespace SymfonyCustom\Tests\NamingConventions;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the ValidFileName sniff.
 *
 * @group SymfonyCustom
 */
class ValidFileNameUnitTest extends AbstractSniffUnitTest
{
    /**
     * @param string $filename
     *
     * @return array
     */
    protected function getErrorList($filename = '')
    {
        switch ($filename) {
            case 'ValidFileNameUnitTest.inc':
                return [];
            case 'ValidFileNameUnitTest.Invalid.inc':
                return [
                    1 => 1,
                ];
            default:
                return [];
        }
    }

    /**
     * @return array
     */
    protected function getWarningList(): array
    {
        return [];
    }
}
