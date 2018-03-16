<?php

namespace SymfonyCustom\Sniffs\Errors;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Checks whether exception messages are concatenated with sprintf
 */
class ExceptionMessageSniff implements Sniff
{
    /**
     * Registers the tokens that this sniff wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(
            T_THROW,
        );
    }

    /**
     * Called when one of the token types that this sniff is listening for is found.
     *
     * @param File $phpcsFile The PHP_CodeSniffer file where the token was found.
     * @param int  $stackPtr  The position in the PHP_CodeSniffer file's token stack where the token was found.
     *
     * @return void|int       Optionally returns a stack pointer. The sniff will not be called again
     *                        on the current file until the returned stack pointer is reached.
     *                        Return (count($tokens) + 1) to skip the rest of the file.
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $opener = $phpcsFile->findNext(T_OPEN_PARENTHESIS, $stackPtr);
        $concat = $phpcsFile->findNext(
            T_STRING_CONCAT,
            $tokens[$opener]['parenthesis_opener'],
            $tokens[$opener]['parenthesis_closer']
        );

        if ($concat) {
            $error = 'Exception and error message strings must be concatenated using sprintf';
            $phpcsFile->addError($error, $stackPtr, 'Invalid');
        }
    }
}
