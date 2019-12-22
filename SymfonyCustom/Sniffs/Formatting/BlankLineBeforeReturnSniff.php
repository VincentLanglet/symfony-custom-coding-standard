<?php

namespace SymfonyCustom\Sniffs\Formatting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Throws errors if there's no blank line before return statements.
 */
class BlankLineBeforeReturnSniff implements Sniff
{
    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_RETURN];
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $current = $stackPtr - 1;
        $prevToken = null;
        $returnOrCommentLine = $tokens[$stackPtr]['line'];

        while ($current >= 0 && null === $prevToken) {
            if (T_WHITESPACE !== $tokens[$current]['code']) {
                if ($this->isComment($tokens[$current])) {
                    if ($returnOrCommentLine > $tokens[$current]['line'] + 1) {
                        $prevToken = $tokens[$current];
                    } else {
                        $returnOrCommentLine = $tokens[$current]['line'];
                    }
                } else {
                    $prevToken = $tokens[$current];
                }
            }
            $current--;
        }

        if (!$prevToken) {
            return;
        }

        if (T_OPEN_CURLY_BRACKET === $prevToken['code'] || T_COLON === $prevToken['code']) {
            return;
        }

        if ($returnOrCommentLine - 1 === $prevToken['line']) {
            $fix = $phpcsFile->addFixableError(
                'Missing blank line before return statement',
                $stackPtr,
                'MissedBlankLineBeforeReturn'
            );

            if ($fix) {
                $phpcsFile->fixer->beginChangeset();
                $i = 1;
                while (T_WHITESPACE === $tokens[$stackPtr - $i]['code']
                    || $this->isComment($tokens[$stackPtr - $i])
                ) {
                    $i++;
                }
                $phpcsFile->fixer->addNewLine($stackPtr - $i);
                $phpcsFile->fixer->endChangeset();
            }
        }

        return;
    }

    /**
     * @param array $token
     *
     * @return bool
     */
    private function isComment(array $token): bool
    {
        return in_array($token['code'], Tokens::$commentTokens);
    }
}
