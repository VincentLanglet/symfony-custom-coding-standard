<?php

declare(strict_types=1);

namespace SymfonyCustom\Sniffs;

use PHP_CodeSniffer\Files\File;

/**
 * class FixerHelper
 *
 * @internal
 */
class FixerHelper
{
    /**
     * @param File $phpcsFile
     * @param int  $fromPtr
     * @param int  $toPtr
     */
    public static function removeAll(File $phpcsFile, int $fromPtr, int $toPtr): void
    {
        $tokens = $phpcsFile->getTokens();

        $phpcsFile->fixer->beginChangeset();

        $i = $fromPtr;
        while (isset($tokens[$i]) && $i < $toPtr) {
            $phpcsFile->fixer->replaceToken($i, '');

            $i++;
        }

        $phpcsFile->fixer->endChangeset();
    }

    /**
     * @param File $phpcsFile
     * @param int  $fromPtr
     * @param int  $fromLine
     * @param int  $toLine
     */
    public static function removeLines(File $phpcsFile, int $fromPtr, int $fromLine, int $toLine): void
    {
        $tokens = $phpcsFile->getTokens();

        $phpcsFile->fixer->beginChangeset();

        $i = $fromPtr;
        while (isset($tokens[$i]) && $tokens[$i]['line'] < $toLine) {
            if ($fromLine <= $tokens[$i]['line']) {
                $phpcsFile->fixer->replaceToken($i, '');
            }

            $i++;
        }

        $phpcsFile->fixer->endChangeset();
    }

    /**
     * @param File       $phpcsFile
     * @param int        $stackPtr
     * @param int        $expected
     * @param int|string $found
     */
    public static function fixWhitespaceBefore(
        File $phpcsFile,
        int $stackPtr,
        int $expected,
        $found
    ): void {
        $phpcsFile->fixer->beginChangeset();

        if (0 === $found) {
            $phpcsFile->fixer->addContent($stackPtr - 1, str_repeat(' ', $expected));
        } else {
            if ('newline' === $found) {
                $prev = $phpcsFile->findPrevious(T_WHITESPACE, $stackPtr - 1, null, true);

                for ($i = $prev + 1; $i < $stackPtr; $i++) {
                    $phpcsFile->fixer->replaceToken($i, '');
                }
            }

            $phpcsFile->fixer->replaceToken($stackPtr - 1, str_repeat(' ', $expected));
        }

        $phpcsFile->fixer->endChangeset();
    }
}
