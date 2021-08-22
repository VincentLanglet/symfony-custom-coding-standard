<?php

declare(strict_types=1);

namespace SymfonyCustom\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

use function mb_strpos;

/**
 * Checks that there is no white space after an opening bracket, for "(", "{", and array bracket.
 * Square Brackets are handled by Squiz_Sniffs_Arrays_ArrayBracketSpacingSniff.
 */
class OpenBracketSpacingSniff implements Sniff
{
    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_OPEN_CURLY_BRACKET, T_OPEN_PARENTHESIS, T_OPEN_SHORT_ARRAY];
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

        if (
            isset($tokens[$stackPtr + 1])
            && T_WHITESPACE === $tokens[$stackPtr + 1]['code']
            && false === mb_strpos($tokens[$stackPtr + 1]['content'], $phpcsFile->eolChar)
        ) {
            $error = 'There should be no space after an opening "%s"';
            $fix = $phpcsFile->addFixableError(
                $error,
                $stackPtr + 1,
                'OpeningWhitespace',
                [$tokens[$stackPtr]['content']]
            );

            if ($fix) {
                $phpcsFile->fixer->replaceToken($stackPtr + 1, '');
            }
        }
    }
}
