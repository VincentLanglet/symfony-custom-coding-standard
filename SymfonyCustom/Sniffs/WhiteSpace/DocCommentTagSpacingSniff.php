<?php

namespace SymfonyCustom\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Checks that there are not 2 empty lines following each other.
 */
class DocCommentTagSpacingSniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(
            T_DOC_COMMENT_TAG
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

        if (!isset($tokens[($stackPtr - 1)]) || T_DOC_COMMENT_WHITESPACE !== $tokens[($stackPtr - 1)]['code']) {
            $error = 'There should be a space before a doc comment tag "%s"';
            $fix = $phpcsFile->addFixableError(
                $error,
                ($stackPtr - 1),
                'DocCommentTagSpacing',
                array($tokens[$stackPtr]['content'])
            );

            if (true === $fix) {
                $phpcsFile->fixer->addContentBefore($stackPtr, ' ');
            }
        } elseif (1 !== $tokens[($stackPtr - 1)]['length']) {
            $error = 'There should be only one space before a doc comment tag "%s"';
            $fix = $phpcsFile->addFixableError(
                $error,
                ($stackPtr + 1),
                'DocCommentTagSpacing',
                array($tokens[$stackPtr]['content'])
            );

            if (true === $fix) {
                $phpcsFile->fixer->replaceToken($stackPtr - 1, ' ');
            }
        }

        // No need to check for space after a doc comment tag
        if (isset($tokens[($stackPtr + 1)])
            && T_DOC_COMMENT_WHITESPACE === $tokens[($stackPtr + 1)]['code']
            && 1 !== $tokens[($stackPtr + 1)]['length']
        ) {
            $error = 'There should be only one space after a doc comment tag "%s"';
            $fix = $phpcsFile->addFixableError(
                $error,
                ($stackPtr + 1),
                'DocCommentTagSpacing',
                array($tokens[$stackPtr]['content'])
            );

            if (true === $fix) {
                $phpcsFile->fixer->replaceToken($stackPtr + 1, ' ');
            }
        }
    }
}
