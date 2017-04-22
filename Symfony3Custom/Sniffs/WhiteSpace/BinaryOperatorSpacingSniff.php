<?php

/**
 * Throws warnings if a binary operator isn't surrounded with whitespace.
 */
class Symfony3Custom_Sniffs_WhiteSpace_BinaryOperatorSpacingSniff
    implements PHP_CodeSniffer_Sniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = array(
                                   'PHP',
                                  );

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return PHP_CodeSniffer_Tokens::$comparisonTokens;

    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if ($tokens[$stackPtr -1]['code'] !== T_WHITESPACE
            || $tokens[$stackPtr +1]['code'] !== T_WHITESPACE
        ) {
            $fix = $phpcsFile->addFixableError(
                'Add a single space around binary operators',
                $stackPtr,
                'Invalid'
            );

            if ($fix === true) {
                if ($tokens[$stackPtr -1]['code'] !== T_WHITESPACE) {
                    $phpcsFile->fixer->addContentBefore($stackPtr, ' ');
                }
                if ($tokens[$stackPtr +1]['code'] !== T_WHITESPACE) {
                    $phpcsFile->fixer->addContent($stackPtr, ' ');
                }
            }
        }
    }
}
