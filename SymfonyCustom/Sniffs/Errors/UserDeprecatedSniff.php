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
     *
     * @return void
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

            $opener = $string + 1;

            if ('E_USER_DEPRECATED' !== $tokens[$string]['content']) {
                continue;
            }

            if ('@' === $tokens[$stackPtr - 1]['content']) {
                continue;
            }

            if ('@' === $tokens[$stackPtr - 2]['content'] && T_NS_SEPARATOR === $tokens[$stackPtr - 1]['code']) {
                continue;
            }

            $phpcsFile->addError(
                'Calls to trigger_error with type E_USER_DEPRECATED must be switched to opt-in via @ operator',
                $stackPtr,
                'Invalid'
            );
            break;
        } while ($opener < $closer);
    }
}
