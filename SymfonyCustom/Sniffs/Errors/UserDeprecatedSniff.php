<?php

declare(strict_types=1);

namespace SymfonyCustom\Sniffs\Errors;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Checks whether E_USER_DEPRECATED errors are silenced by default.
 */
class UserDeprecatedSniff implements Sniff
{
    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_STRING];
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();

        if ('trigger_error' !== $tokens[$stackPtr]['content']) {
            return;
        }

        $pos = $phpcsFile->findNext(T_OPEN_PARENTHESIS, $stackPtr);
        $opener = $tokens[$pos]['parenthesis_opener'];
        $closer = $tokens[$pos]['parenthesis_closer'];

        do {
            $string = $phpcsFile->findNext(T_STRING, $opener, $closer);

            if (false === $string) {
                break;
            }

            if ('E_USER_DEPRECATED' === $tokens[$string]['content'] && '@' !== $tokens[$stackPtr - 1]['content']) {
                $phpcsFile->addError(
                    'Calls to trigger_error with type E_USER_DEPRECATED must be switched to opt-in via @ operator',
                    $stackPtr,
                    'Invalid'
                );

                break;
            } else {
                $opener = $string + 1;
            }
        } while ($opener < $closer);
    }
}
