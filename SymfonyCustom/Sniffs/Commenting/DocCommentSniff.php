<?php

declare(strict_types=1);

namespace SymfonyCustom\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SymfonyCustom\Sniffs\FixerHelper;

/**
 * Ensures doc blocks follow basic formatting.
 */
class DocCommentSniff implements Sniff
{
    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_DOC_COMMENT_OPEN_TAG];
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();

        if (!isset($tokens[$stackPtr]['comment_closer'])
            || ('' === $tokens[$tokens[$stackPtr]['comment_closer']]['content']
                && $phpcsFile->numTokens - 1 === $tokens[$stackPtr]['comment_closer'])
        ) {
            // Don't process an unfinished comment during live coding.
            return;
        }

        $commentEnd = $tokens[$stackPtr]['comment_closer'];

        $empty = [T_DOC_COMMENT_WHITESPACE, T_DOC_COMMENT_STAR];

        $short = $phpcsFile->findNext($empty, $stackPtr + 1, $commentEnd, true);
        if (false === $short) {
            // No content at all.
            $next = $phpcsFile->findNext(T_WHITESPACE, $commentEnd + 1, null, true);
            $hasSameLineNext = $next && $tokens[$next]['line'] === $tokens[$commentEnd]['line'];

            $fix = $phpcsFile->addFixableError('Doc comment is empty', $stackPtr, 'Empty');

            if ($fix) {
                $phpcsFile->fixer->beginChangeset();

                $end = $hasSameLineNext ? $next : $commentEnd + 1;
                for ($i = $stackPtr; $i < $end; $i++) {
                    $phpcsFile->fixer->replaceToken($i, '');
                }

                if (!$hasSameLineNext) {
                    $previous = $phpcsFile->findPrevious(T_WHITESPACE, $stackPtr - 1, null, true);
                    for ($i = $stackPtr - 1; $i > $previous; $i--) {
                        if ($tokens[$i]['line'] < $tokens[$stackPtr]['line']) {
                            $phpcsFile->fixer->replaceToken($i, '');
                            break;
                        }

                        $phpcsFile->fixer->replaceToken($i, '');
                    }
                }

                $phpcsFile->fixer->endChangeset();
            }

            return;
        }

        $isSingleLine = $tokens[$stackPtr]['line'] === $tokens[$commentEnd]['line'];

        // The first line of the comment should just be the /** code.
        if (!$isSingleLine && $tokens[$short]['line'] === $tokens[$stackPtr]['line']) {
            $fix = $phpcsFile->addFixableError(
                'The open comment tag must be the only content on the line',
                $stackPtr,
                'ContentAfterOpen'
            );

            if ($fix) {
                $phpcsFile->fixer->beginChangeset();
                for ($i = $stackPtr + 1; $i < $short; $i++) {
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
        if ($tokens[$stackPtr]['line'] < $tokens[$short]['line'] - 1) {
            $fix = $phpcsFile->addFixableError(
                'Additional blank lines found at beginning of doc comment',
                $stackPtr,
                'SpacingBefore'
            );

            if ($fix) {
                FixerHelper::removeAll($phpcsFile, $stackPtr + 1, $short);
            }
        }

        // The last line of the comment should just be the */ code.
        $prev = $phpcsFile->findPrevious($empty, $commentEnd - 1, $stackPtr, true);
        if (!$isSingleLine && $tokens[$prev]['line'] === $tokens[$commentEnd]['line']) {
            $fix = $phpcsFile->addFixableError(
                'The close comment tag must be the only content on the line',
                $commentEnd,
                'ContentBeforeClose'
            );

            if ($fix) {
                $phpcsFile->fixer->beginChangeset();
                for ($i = $prev + 1; $i < $commentEnd; $i++) {
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
        if ($tokens[$prev]['line'] < $tokens[$commentEnd]['line'] - 1) {
            $fix = $phpcsFile->addFixableError(
                'Additional blank lines found at end of doc comment',
                $commentEnd,
                'SpacingAfter'
            );

            if ($fix) {
                FixerHelper::removeAll($phpcsFile, $prev + 1, $commentEnd);
            }
        }
    }
}
