<?php

namespace SymfonyCustom\Sniffs\Classes;

use PHP_CodeSniffer\Files\File;

/**
 * Checks that there are not empty lines following class declaration.
 */
class ClassDeclarationSniff
{
    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_CLASS, T_INTERFACE, T_TRAIT];
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        // Just in case.
        $tokens = $phpcsFile->getTokens();
        $openingBrace = $tokens[$stackPtr]['scope_opener'];

        if (!isset($openingBrace)) {
            return;
        }

        $nextElement = $phpcsFile->findNext(T_WHITESPACE, $openingBrace + 1, null, true);

        if ($tokens[$openingBrace]['line'] + 1 < $tokens[$nextElement]['line']) {
            $fix = $phpcsFile->addFixableError(
                'The opening brace should not be followed by a blank line',
                $openingBrace,
                'Invalid'
            );

            if ($fix) {
                $phpcsFile->fixer->replaceToken($openingBrace + 1, '');
            }
        }
    }
}
