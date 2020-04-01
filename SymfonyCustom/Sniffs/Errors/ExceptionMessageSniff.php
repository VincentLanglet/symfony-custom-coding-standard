<?php

declare(strict_types=1);

namespace SymfonyCustom\Sniffs\Errors;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Checks whether exception messages are concatenated with sprintf
 */
class ExceptionMessageSniff implements Sniff
{
    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_THROW];
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $opener = $phpcsFile->findNext(T_OPEN_PARENTHESIS, $stackPtr);
        $concat = $phpcsFile->findNext(
            T_STRING_CONCAT,
            $tokens[$opener]['parenthesis_opener'],
            $tokens[$opener]['parenthesis_closer']
        );

        if ($concat) {
            $phpcsFile->addError(
                'Exception and error message strings must be concatenated using sprintf',
                $stackPtr,
                'Invalid'
            );
        }
    }
}
