<?php

declare(strict_types=1);

namespace SymfonyCustom\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SymfonyCustom\Helpers\SniffHelper;

/**
 * Checks that there are not 2 empty lines following each other.
 */
class DocCommentTagSpacingSniff implements Sniff
{
    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_DOC_COMMENT_TAG];
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

        if (isset($tokens[$stackPtr - 1])
            && $tokens[$stackPtr]['line'] === $tokens[$stackPtr - 1]['line']
        ) {
            if (T_DOC_COMMENT_WHITESPACE !== $tokens[$stackPtr - 1]['code']) {
                $error = 'There should be a space before a doc comment tag "%s"';
                $fix = $phpcsFile->addFixableError(
                    $error,
                    $stackPtr - 1,
                    'DocCommentTagSpacing',
                    [$tokens[$stackPtr]['content']]
                );

                if ($fix) {
                    $phpcsFile->fixer->addContentBefore($stackPtr, ' ');
                }
            } elseif (1 < $tokens[$stackPtr - 1]['length']) {
                $isCustomTag = !in_array($tokens[$stackPtr]['content'], SniffHelper::TAGS);

                // Custom tags are not checked cause there is annotation with array params
                if (!$isCustomTag) {
                    $error = 'There should be only one space before a doc comment tag "%s"';
                    $fix = $phpcsFile->addFixableError(
                        $error,
                        $stackPtr + 1,
                        'DocCommentTagSpacing',
                        [$tokens[$stackPtr]['content']]
                    );

                    if ($fix) {
                        $phpcsFile->fixer->replaceToken($stackPtr - 1, ' ');
                    }
                }
            }
        }

        if (isset($tokens[$stackPtr + 1])
            && $tokens[$stackPtr]['line'] === $tokens[$stackPtr + 1]['line']
            && T_DOC_COMMENT_WHITESPACE === $tokens[$stackPtr + 1]['code']
            && 1 < $tokens[$stackPtr + 1]['length']
        ) {
            $error = 'There should be only one space after a doc comment tag "%s"';
            $fix = $phpcsFile->addFixableError(
                $error,
                $stackPtr + 1,
                'DocCommentTagSpacing',
                [$tokens[$stackPtr]['content']]
            );

            if ($fix) {
                $phpcsFile->fixer->replaceToken($stackPtr + 1, ' ');
            }
        }
    }
}
