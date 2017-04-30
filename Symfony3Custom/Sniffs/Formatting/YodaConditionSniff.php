<?php

/**
 * Enforces Yoda conditional statements.
 */
class Symfony3Custom_Sniffs_Formatting_YodaConditionSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(
            T_IS_EQUAL,
            T_IS_NOT_EQUAL,
            T_IS_IDENTICAL,
            T_IS_NOT_IDENTICAL,
        );
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token in the
     *                                        stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $beginners   = PHP_CodeSniffer_Tokens::$booleanOperators;
        $beginners[] = T_IF;
        $beginners[] = T_ELSEIF;

        $beginning = $phpcsFile->findPrevious($beginners, $stackPtr, null, false, null, true);

        $needsYoda = false;

        // Note: going backwards!
        for ($i = $stackPtr; $i > $beginning; $i--) {
            // Ignore whitespace.
            if (isset(PHP_CodeSniffer_Tokens::$emptyTokens[$tokens[$i]['code']])) {
                continue;
            }

            // If this is a variable or array, we've seen all we need to see.
            if (T_VARIABLE === $tokens[$i]['code'] || T_CLOSE_SQUARE_BRACKET === $tokens[$i]['code']) {
                $needsYoda = true;
                break;
            }

            // If this is a function call or something, we are OK.
            if (in_array(
                $tokens[$i]['code'],
                array(T_CONSTANT_ENCAPSED_STRING, T_CLOSE_PARENTHESIS, T_OPEN_PARENTHESIS, T_RETURN),
                true
            )) {
                return;
            }
        }

        if (!$needsYoda) {
            return;
        }

        // Check if this is a var to var comparison, e.g.: if ( $var1 == $var2 ).
        $nextNonEmpty = $phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($stackPtr + 1), null, true);

        if (isset(PHP_CodeSniffer_Tokens::$castTokens[$tokens[$nextNonEmpty]['code']])) {
            $nextNonEmpty = $phpcsFile->findNext(
                PHP_CodeSniffer_Tokens::$emptyTokens,
                ($nextNonEmpty + 1),
                null,
                true
            );
        }

        if (in_array($tokens[$nextNonEmpty]['code'], array(T_SELF, T_PARENT, T_STATIC), true)) {
            $nextNonEmpty = $phpcsFile->findNext(
                array_merge(PHP_CodeSniffer_Tokens::$emptyTokens, array(T_DOUBLE_COLON)),
                ($nextNonEmpty + 1),
                null,
                true
            );
        }

        if (T_VARIABLE === $tokens[$nextNonEmpty]['code']) {
            return;
        }

        $phpcsFile->addError('Use Yoda Condition checks, you must.', $stackPtr, 'NotYoda');
    }
}
