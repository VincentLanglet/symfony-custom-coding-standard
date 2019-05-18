<?php

namespace SymfonyCustom\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Ensures doc blocks follow basic formatting.
 */
class DocCommentSniff implements Sniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = [
        'PHP',
        'JS',
    ];

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [T_DOC_COMMENT_OPEN_TAG];
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

        if (false === isset($tokens[$stackPtr]['comment_closer'])
            || ('' === $tokens[$tokens[$stackPtr]['comment_closer']]['content']
            && ($phpcsFile->numTokens - 1) === $tokens[$stackPtr]['comment_closer'])
        ) {
            // Don't process an unfinished comment during live coding.
            return;
        }

        $commentEnd = $tokens[$stackPtr]['comment_closer'];

        $empty = [
            T_DOC_COMMENT_WHITESPACE,
            T_DOC_COMMENT_STAR,
        ];

        $short = $phpcsFile->findNext($empty, ($stackPtr + 1), $commentEnd, true);
        if (false === $short) {
            // No content at all.
            $error = 'Doc comment is empty';
            $phpcsFile->addError($error, $stackPtr, 'Empty');

            return;
        }

        $isSingleLine = $tokens[$stackPtr]['line'] === $tokens[$commentEnd]['line'];

        // The first line of the comment should just be the /** code.
        if (!$isSingleLine && $tokens[$short]['line'] === $tokens[$stackPtr]['line']) {
            $error = 'The open comment tag must be the only content on the line';
            $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'ContentAfterOpen');
            if (true === $fix) {
                $phpcsFile->fixer->beginChangeset();
                for ($i = ($stackPtr + 1); $i < $short; $i++) {
                    $phpcsFile->fixer->replaceToken($i, '');
                }
                $phpcsFile->fixer->addNewline($stackPtr);
                $phpcsFile->fixer->replaceToken(
                    $short,
                    ltrim($tokens[$short]['content'])
                );
                $phpcsFile->fixer->addContentBefore(
                    $short,
                    str_repeat(' ', $tokens[$stackPtr]['column']).'* '
                );
                $phpcsFile->fixer->endChangeset();
            }
        }

        // Check for additional blank lines at the beginning of the comment.
        if ($tokens[$stackPtr]['line'] < ($tokens[$short]['line'] - 1)) {
            $error = 'Additional blank lines found at beginning of doc comment';
            $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'SpacingBefore');
            if (true === $fix) {
                $phpcsFile->fixer->beginChangeset();
                for ($i = ($stackPtr + 1); $i < $short; $i++) {
                    if ($tokens[($i + 1)]['line'] === $tokens[$short]['line']) {
                        break;
                    }

                    $phpcsFile->fixer->replaceToken($i, '');
                }

                $phpcsFile->fixer->endChangeset();
            }
        }

        // The last line of the comment should just be the */ code.
        $prev = $phpcsFile->findPrevious($empty, ($commentEnd - 1), $stackPtr, true);
        if (!$isSingleLine && $tokens[$prev]['line'] === $tokens[$commentEnd]['line']) {
            $error = 'The close comment tag must be the only content on the line';
            $fix   = $phpcsFile->addFixableError($error, $commentEnd, 'ContentBeforeClose');
            if (true === $fix) {
                $phpcsFile->fixer->beginChangeset();
                for ($i = ($prev + 1); $i < $commentEnd; $i++) {
                    $phpcsFile->fixer->replaceToken($i, '');
                }
                $phpcsFile->fixer->replaceToken(
                    $commentEnd - 1,
                    rtrim($tokens[$commentEnd - 1]['content'])
                );
                $phpcsFile->fixer->addContentBefore(
                    $commentEnd,
                    str_repeat(' ', $tokens[$stackPtr]['column'])
                );
                $phpcsFile->fixer->addNewlineBefore($commentEnd);
                $phpcsFile->fixer->endChangeset();
            }
        }

        // Check for additional blank lines at the end of the comment.
        if ($tokens[$prev]['line'] < ($tokens[$commentEnd]['line'] - 1)) {
            $error = 'Additional blank lines found at end of doc comment';
            $fix   = $phpcsFile->addFixableError($error, $commentEnd, 'SpacingAfter');
            if (true === $fix) {
                $phpcsFile->fixer->beginChangeset();
                for ($i = ($prev + 1); $i < $commentEnd; $i++) {
                    if ($tokens[($i + 1)]['line'] === $tokens[$commentEnd]['line']) {
                        break;
                    }

                    $phpcsFile->fixer->replaceToken($i, '');
                }

                $phpcsFile->fixer->endChangeset();
            }
        }
    }
}
