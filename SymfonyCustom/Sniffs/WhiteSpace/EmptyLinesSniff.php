<?php

namespace SymfonyCustom\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Checks that there are not 2 empty lines following each other.
 */
class EmptyLinesSniff implements Sniff
{
    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_WHITESPACE];
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();

        // Special case for the first line
        if (isset($tokens[$stackPtr - 1])
            && 'T_OPEN_TAG' === $tokens[$stackPtr - 1]['type']
            && $tokens[$stackPtr]['content'] === $phpcsFile->eolChar
            && isset($tokens[$stackPtr + 1])
            && $tokens[$stackPtr + 1]['content'] === $phpcsFile->eolChar
        ) {
            $error = 'More than 1 empty lines are not allowed';
            $fix = $phpcsFile->addFixableError($error, $stackPtr + 1, 'EmptyLines');

            if ($fix) {
                $phpcsFile->fixer->replaceToken($stackPtr + 1, '');
            }
        }

        // General case
        if ($tokens[$stackPtr]['content'] === $phpcsFile->eolChar
            && isset($tokens[$stackPtr + 1])
            && $tokens[$stackPtr + 1]['content'] === $phpcsFile->eolChar
            && isset($tokens[$stackPtr + 2])
            && $tokens[$stackPtr + 2]['content'] === $phpcsFile->eolChar
        ) {
            $error = 'More than 1 empty lines are not allowed';
            $fix = $phpcsFile->addFixableError($error, $stackPtr + 2, 'EmptyLines');

            if ($fix) {
                $phpcsFile->fixer->replaceToken($stackPtr + 2, '');
            }
        }
    }
}
