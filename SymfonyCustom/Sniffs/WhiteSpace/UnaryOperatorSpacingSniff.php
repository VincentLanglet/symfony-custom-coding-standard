<?php

declare(strict_types=1);

namespace SymfonyCustom\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

use function in_array;

/**
 * Ensures there are no spaces +/- sign operators.
 */
class UnaryOperatorSpacingSniff implements Sniff
{
    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_MINUS, T_PLUS];
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

        // Find the last syntax item to determine if this is an unary operator.
        $lastSyntaxItem = $phpcsFile->findPrevious(
            T_WHITESPACE,
            $stackPtr - 1,
            ($tokens[$stackPtr]['column']) * -1,
            true,
            null,
            true
        );
        $operatorSuffixAllowed = in_array(
            $tokens[$lastSyntaxItem]['code'],
            [
                T_LNUMBER,
                T_DNUMBER,
                T_CLOSE_PARENTHESIS,
                T_CLOSE_CURLY_BRACKET,
                T_CLOSE_SQUARE_BRACKET,
                T_VARIABLE,
                T_STRING,
            ]
        );

        // Check plus / minus value assignments or comparisons.
        if (!$operatorSuffixAllowed && T_WHITESPACE === $tokens[$stackPtr + 1]['code']) {
            $fix = $phpcsFile->addFixableError(
                'A unary operator statement must not be followed by a space',
                $stackPtr,
                'Invalid'
            );

            if ($fix) {
                $phpcsFile->fixer->replaceToken($stackPtr + 1, '');
            }
        }
    }
}
