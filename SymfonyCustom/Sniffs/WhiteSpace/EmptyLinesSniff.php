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
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [
            T_WHITESPACE
        ];
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Special case for the first line
        if (isset($tokens[$stackPtr - 1])
            && 'T_OPEN_TAG' === $tokens[$stackPtr - 1]['type']
            && $tokens[$stackPtr]['content'] === $phpcsFile->eolChar
            && isset($tokens[$stackPtr + 1]) === true
            && $tokens[$stackPtr + 1]['content'] === $phpcsFile->eolChar
        ) {
            $error = 'More than 1 empty lines are not allowed';
            $fix = $phpcsFile->addFixableError($error, $stackPtr + 1, 'EmptyLines');

            if (true === $fix) {
                $phpcsFile->fixer->replaceToken($stackPtr + 1, '');
            }
        }

        // General case
        if ($tokens[$stackPtr]['content'] === $phpcsFile->eolChar
            && isset($tokens[$stackPtr + 1]) === true
            && $tokens[$stackPtr + 1]['content'] === $phpcsFile->eolChar
            && isset($tokens[$stackPtr + 2]) === true
            && $tokens[$stackPtr + 2]['content'] === $phpcsFile->eolChar
        ) {
            $error = 'More than 1 empty lines are not allowed';
            $fix = $phpcsFile->addFixableError($error, $stackPtr + 2, 'EmptyLines');

            if (true === $fix) {
                $phpcsFile->fixer->replaceToken($stackPtr + 2, '');
            }
        }
    }
}
