<?php

namespace Symfony3Custom\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Ensures there are no spaces on increment / decrement statements or on +/- sign
 * operators or "!" boolean negators.
 */
class UnaryOperatorSpacingSniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(
            T_DEC,
            T_INC,
            T_MINUS,
            T_PLUS,
            T_BOOLEAN_NOT,
        );
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Check decrement / increment.
        if (T_DEC === $tokens[$stackPtr]['code'] || T_INC === $tokens[$stackPtr]['code']) {
            $modifyLeft = substr($tokens[($stackPtr - 1)]['content'], 0, 1) === '$'
                || ';' === $tokens[($stackPtr + 1)]['content'];

            if (true === $modifyLeft && T_WHITESPACE === $tokens[($stackPtr - 1)]['code']) {
                $error = 'There must not be a single space before a unary operator statement';
                $fix = $phpcsFile->addFixableError($error, $stackPtr, 'IncDecLeft');

                if (true === $fix) {
                    $phpcsFile->fixer->replaceToken($stackPtr - 1, '');
                }

                return;
            }

            if (false === $modifyLeft && substr($tokens[($stackPtr + 1)]['content'], 0, 1) !== '$') {
                $error = 'A unary operator statement must not be followed by a single space';
                $fix = $phpcsFile->addFixableError($error, $stackPtr, 'IncDecRight');

                if (true === $fix) {
                    $phpcsFile->fixer->replaceToken($stackPtr + 1, '');
                }

                return;
            }
        }

        // Check "!" operator.
        if (T_BOOLEAN_NOT === $tokens[$stackPtr]['code'] && T_WHITESPACE === $tokens[$stackPtr + 1]['code']) {
            $error = 'A unary operator statement must not be followed by a space';
            $fix = $phpcsFile->addFixableError($error, $stackPtr, 'BooleanNot');

            if (true === $fix) {
                $phpcsFile->fixer->replaceToken($stackPtr + 1, '');
            }

            return;
        }

        // Find the last syntax item to determine if this is an unary operator.
        $lastSyntaxItem = $phpcsFile->findPrevious(
            array(T_WHITESPACE),
            $stackPtr - 1,
            ($tokens[$stackPtr]['column']) * -1,
            true,
            null,
            true
        );
        $operatorSuffixAllowed = in_array(
            $tokens[$lastSyntaxItem]['code'],
            array(
                T_LNUMBER,
                T_DNUMBER,
                T_CLOSE_PARENTHESIS,
                T_CLOSE_CURLY_BRACKET,
                T_CLOSE_SQUARE_BRACKET,
                T_VARIABLE,
                T_STRING,
            )
        );

        // Check plus / minus value assignments or comparisons.
        if (T_MINUS === $tokens[$stackPtr]['code'] || T_PLUS === $tokens[$stackPtr]['code']) {
            if (false === $operatorSuffixAllowed
                && T_WHITESPACE === $tokens[($stackPtr + 1)]['code']
            ) {
                $error = 'A unary operator statement must not be followed by a space';
                $fix = $phpcsFile->addFixableError($error, $stackPtr, 'Invalid');

                if (true === $fix) {
                    $phpcsFile->fixer->replaceToken($stackPtr + 1, '');
                }
            }
        }
    }
}
