<?php

declare(strict_types=1);

namespace SymfonyCustom\Sniffs\NamingConventions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Checks whether filename contains any other character than alphanumeric and underscores.
 */
class ValidFileNameSniff implements Sniff
{
    /**
     * @return int[]
     */
    public function register()
    {
        return [T_OPEN_TAG];
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $filename = $phpcsFile->getFilename();

        if ('STDIN' === $filename) {
            return;
        }

        $filenamePhp = basename($filename, '.php');
        $filenameInc = basename($filename, '.inc');

        if (strlen($filenameInc) < strlen($filenamePhp)) {
            $filename = $filenameInc;
        } else {
            $filename = $filenamePhp;
        }

        if (!ctype_alnum($filename)) {
            $phpcsFile->addError(
                'Filename "%s" contains non alphanumeric characters',
                $stackPtr,
                'Invalid',
                [$filename]
            );
        }
    }
}
