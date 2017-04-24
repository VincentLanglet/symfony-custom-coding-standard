<?php

/**
 * Checks that there is no white space after an opening bracket, for "(" and "{".
 * Square Brackets are handled by Squiz_Sniffs_Arrays_ArrayBracketSpacingSniff.
 */
class Symfony3Custom_Sniffs_WhiteSpace_OpenBracketSpacingSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = array(
        'PHP',
        'JS',
    );

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(
            T_OPEN_CURLY_BRACKET,
            T_OPEN_PARENTHESIS,
        );

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

        // Ignore curly brackets in javascript files.
        if ($tokens[$stackPtr]['code'] === T_OPEN_CURLY_BRACKET
            && $phpcsFile->tokenizerType === 'JS'
        ) {
            return;
        }

        if (isset($tokens[($stackPtr + 1)]) === true
            && $tokens[($stackPtr + 1)]['code'] === T_WHITESPACE
            && strpos($tokens[($stackPtr + 1)]['content'], $phpcsFile->eolChar) === false
        ) {
            $error = 'There should be no space after an opening "%s"';
            $phpcsFile->addError(
                $error,
                ($stackPtr + 1),
                'OpeningWhitespace',
                array($tokens[$stackPtr]['content'])
            );
        }
    }
}
