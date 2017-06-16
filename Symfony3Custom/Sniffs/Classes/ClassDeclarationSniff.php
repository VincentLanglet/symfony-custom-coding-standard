<?php

namespace Symfony3Custom\Sniffs\Classes;

use PHP_CodeSniffer\Files\File;

class ClassDeclarationSniff
{
    /**
     * @return array
     */
    public function register()
    {
        return array(
            T_CLASS,
            T_INTERFACE,
            T_TRAIT,
        );
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int  $stackPtr  The position of the current token
     *                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        // Just in case.
        $tokens = $phpcsFile->getTokens();
        $openingBrace = $tokens[$stackPtr]['scope_opener'];

        if (isset($openingBrace) === false) {
            return;
        }

        $nextElement = $phpcsFile->findNext(array(T_WHITESPACE), $openingBrace + 1, null, true);

        if ($tokens[$openingBrace]['line'] + 1 < $tokens[$nextElement]['line']) {
            $fix = $phpcsFile->addFixableError(
                'The opening brace should not be followed by a blank line',
                $openingBrace,
                'Invalid'
            );

            if (true === $fix) {
                $phpcsFile->fixer->replaceToken($openingBrace + 1, '');
            }
        }
    }
}
