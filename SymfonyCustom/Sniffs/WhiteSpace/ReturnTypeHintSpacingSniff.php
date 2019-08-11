<?php

namespace SymfonyCustom\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Checks that there are no white space before and one space after : in the return type hint.
 */
class ReturnTypeHintSpacingSniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [
            T_COLON,
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

        if (isset($tokens[($stackPtr - 1)]) && T_WHITESPACE === $tokens[($stackPtr - 1)]['code']) {
            $error = 'There should be no space before a colon';
            $fix = $phpcsFile->addFixableError($error, $stackPtr, 'SpaceBeforeReturnTypeHint');

            if (true === $fix) {
                $phpcsFile->fixer->replaceToken($stackPtr - 1, '');
            }
        }

        if (isset($tokens[($stackPtr + 1)]) && T_WHITESPACE !== $tokens[($stackPtr + 1)]['code']) {
            $error = 'There should be a space after a colon';
            $fix = $phpcsFile->addFixableError($error, $stackPtr, 'SpaceAfterReturnTypeHint');

            if (true === $fix) {
                $phpcsFile->fixer->addContent($stackPtr, ' ');
            }
        }
    }
}
