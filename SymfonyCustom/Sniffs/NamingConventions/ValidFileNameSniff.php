<?php

namespace SymfonyCustom\Sniffs\NamingConventions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Checks whether filename contains any other character than alphanumeric and underscores.
 */
class ValidFileNameSniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [T_OPEN_TAG];
    }

    /**
     * Process.
     *
     * @param File $phpcsFile
     * @param int  $stackPtr
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
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

        if (false === ctype_alnum($filename)) {
            $error = sprintf('Filename "%s" contains non alphanumeric characters', $filename);
            $phpcsFile->addError($error, $stackPtr, 'Invalid');
            $phpcsFile->recordMetric($stackPtr, 'Alphanumeric filename', 'no');
        } else {
            $phpcsFile->recordMetric($stackPtr, 'Alphanumeric filename', 'yes');
        }

        return;
    }
}
