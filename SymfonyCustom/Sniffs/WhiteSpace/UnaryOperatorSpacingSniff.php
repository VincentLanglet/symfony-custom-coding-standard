<?php

namespace SymfonyCustom\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Ensures there are no spaces on ++/-- or on +/- sign operators or "!" boolean negators.
 */
class UnaryOperatorSpacingSniff implements Sniff
{
    /**
     * @return int[]
     */
    public function register(): array
    {
        return [
            T_DEC,
            T_INC,
            T_MINUS,
            T_PLUS,
            T_BOOLEAN_NOT,
        ];
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();

        // Check decrement / increment.
        if (T_DEC === $tokens[$stackPtr]['code'] || T_INC === $tokens[$stackPtr]['code']) {
            $modifyLeft = substr($tokens[($stackPtr - 1)]['content'], 0, 1) === '$'
                || ';' === $tokens[($stackPtr + 1)]['content'];

            if ($modifyLeft && T_WHITESPACE === $tokens[($stackPtr - 1)]['code']) {
                $error = 'There must not be a single space before a unary operator statement';
                $fix = $phpcsFile->addFixableError($error, $stackPtr, 'IncDecLeft');

                if ($fix) {
                    $phpcsFile->fixer->replaceToken($stackPtr - 1, '');
                }

                return;
            }

            if (!$modifyLeft && substr($tokens[($stackPtr + 1)]['content'], 0, 1) !== '$') {
                $error = 'A unary operator statement must not be followed by a single space';
                $fix = $phpcsFile->addFixableError($error, $stackPtr, 'IncDecRight');

                if ($fix) {
                    $phpcsFile->fixer->replaceToken($stackPtr + 1, '');
                }

                return;
            }
        }

        // Check "!" operator.
        if (T_BOOLEAN_NOT === $tokens[$stackPtr]['code'] && T_WHITESPACE === $tokens[$stackPtr + 1]['code']) {
            $error = 'A unary operator statement must not be followed by a space';
            $fix = $phpcsFile->addFixableError($error, $stackPtr, 'BooleanNot');

            if ($fix) {
                $phpcsFile->fixer->replaceToken($stackPtr + 1, '');
            }

            return;
        }

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
        if (T_MINUS === $tokens[$stackPtr]['code'] || T_PLUS === $tokens[$stackPtr]['code']) {
            if (!$operatorSuffixAllowed
                && T_WHITESPACE === $tokens[($stackPtr + 1)]['code']
            ) {
                $error = 'A unary operator statement must not be followed by a space';
                $fix = $phpcsFile->addFixableError($error, $stackPtr, 'Invalid');

                if ($fix) {
                    $phpcsFile->fixer->replaceToken($stackPtr + 1, '');
                }
            }
        }
    }
}
