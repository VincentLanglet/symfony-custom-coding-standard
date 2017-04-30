<?php

/**
 * Throws errors if there's no blank line before return statements.
 * Symfony coding standard specifies: "Add a blank line before return statements,
 * unless the return is alone inside a statement-group (like an if statement);"
 */
class Symfony3Custom_Sniffs_Formatting_BlankLineBeforeReturnSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(
            T_RETURN
        );
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile All the tokens found in the document.
     * @param int                  $stackPtr  The position of the current token in
     *                                        the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $current = $stackPtr;
        $previousLine = $tokens[$stackPtr]['line'] - 1;
        $prevLineTokens  = array();

        while ($current >= 0 && $tokens[$current]['line'] >= $previousLine) {
            if ($tokens[$current]['line'] == $previousLine
                && 'T_WHITESPACE' !== $tokens[$current]['type']
                && 'T_COMMENT' !== $tokens[$current]['type']
                && 'T_DOC_COMMENT_CLOSE_TAG' !== $tokens[$current]['type']
                && 'T_DOC_COMMENT_WHITESPACE' !== $tokens[$current]['type']
            ) {
                $prevLineTokens[] = $tokens[$current]['type'];
            }
            $current--;
        }

        if (isset($prevLineTokens[0])
            && ('T_OPEN_CURLY_BRACKET' === $prevLineTokens[0]
            || 'T_COLON' === $prevLineTokens[0])
        ) {
            return;
        } elseif (count($prevLineTokens) > 0) {
            $fix = $phpcsFile->addFixableError(
                'Missing blank line before return statement',
                $stackPtr,
                'MissedBlankLineBeforeRetrun'
            );

            if (true === $fix) {
                $phpcsFile->fixer->beginChangeset();
                $i = 1;
                while ('T_WHITESPACE' == $tokens[$stackPtr - $i]['type']) {
                    $i++;
                }
                $phpcsFile->fixer->addNewLine($stackPtr - $i);
                $phpcsFile->fixer->endChangeset();
            }
        }

        return;
    }
}
