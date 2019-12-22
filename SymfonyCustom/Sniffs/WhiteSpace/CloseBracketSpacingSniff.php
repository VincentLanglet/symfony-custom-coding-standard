<?php

namespace SymfonyCustom\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Checks that there is no white space before a closing bracket, for ")", "}", and array bracket.
 * Square Brackets are handled by Squiz\Sniffs\Arrays\ArrayBracketSpacingSniff.
 */
class CloseBracketSpacingSniff implements Sniff
{
    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_CLOSE_CURLY_BRACKET, T_CLOSE_PARENTHESIS, T_CLOSE_SHORT_ARRAY];
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();

        if (isset($tokens[($stackPtr - 1)]) && T_WHITESPACE === $tokens[($stackPtr - 1)]['code']) {
            $before = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($stackPtr - 1), null, true);
            if (false !== $before && $tokens[$stackPtr]['line'] === $tokens[$before]['line']) {
                $error = 'There should be no space before a closing "%s"';
                $fix = $phpcsFile->addFixableError(
                    $error,
                    ($stackPtr - 1),
                    'ClosingWhitespace',
                    [$tokens[$stackPtr]['content']]
                );

                if ($fix) {
                    $phpcsFile->fixer->replaceToken($stackPtr - 1, '');
                }
            }
        }
    }
}
