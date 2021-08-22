<?php

declare(strict_types=1);

namespace SymfonyCustom\Sniffs\Operators;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Checks that the operator is at the start of the line.
 */
class OperatorPlacementSniff implements Sniff
{
    /**
     * @return int[]
     */
    public function register(): array
    {
        $targets = Tokens::$comparisonTokens;
        $targets += Tokens::$operators;
        $targets[] = T_STRING_CONCAT;
        $targets[] = T_INLINE_THEN;
        $targets[] = T_INLINE_ELSE;

        return $targets;
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

        $next = $phpcsFile->findNext(Tokens::$emptyTokens, $stackPtr + 1, null, true);

        if ($tokens[$stackPtr]['line'] === $tokens[$next]['line']) {
            return;
        }

        $content = $tokens[$stackPtr]['content'];
        $error = 'Operator "%s" should be on the start of the next line';
        $fix = $phpcsFile->addFixableError($error, $stackPtr, 'OperatorPlacement', [$content]);

        if ($fix) {
            $phpcsFile->fixer->beginChangeset();
            $phpcsFile->fixer->replaceToken($stackPtr, '');
            $phpcsFile->fixer->addContentBefore($next, $content);
            $phpcsFile->fixer->endChangeset();
        }
    }
}
