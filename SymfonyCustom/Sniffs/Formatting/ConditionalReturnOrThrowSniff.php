<?php

declare(strict_types=1);

namespace SymfonyCustom\Sniffs\Formatting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Checks whether there are else(if) or break statements after return or throw
 */
class ConditionalReturnOrThrowSniff implements Sniff
{
    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_THROW, T_RETURN];
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
        $opener = $phpcsFile->findPrevious([T_IF, T_CASE], $stackPtr);

        if (
            false !== $opener
            && isset($tokens[$opener]['scope_closer'])
            && $stackPtr <= $tokens[$opener]['scope_closer']
        ) {
            $isClosure = $phpcsFile->findPrevious(T_CLOSURE, $stackPtr, $opener);
            if (false !== $isClosure) {
                return;
            }

            $isTryCatch = $phpcsFile->findPrevious([T_TRY, T_CATCH], $stackPtr, $opener);
            if (false !== $isTryCatch) {
                return;
            }

            $condition = $phpcsFile->findNext([T_ELSEIF, T_ELSE, T_BREAK], $stackPtr + 1);
            if (false !== $condition) {
                $start = $stackPtr;
                $end = $condition;

                $next = $phpcsFile->findNext([T_IF, T_CASE], $start + 1, $end);
                while (false !== $next) {
                    if ($tokens[$condition]['level'] >= $tokens[$next]['level']) {
                        $err = false;
                        break;
                    }

                    $start = $next;
                    $next = $phpcsFile->findNext([T_IF, T_CASE], $start + 1, $end);
                }

                if (!isset($err)) {
                    $err = $tokens[$condition]['level'] === $tokens[$opener]['level'];
                }

                if ($err) {
                    $phpcsFile->addError(
                        'Do not use else, elseif, break after if and case conditions which return or throw something',
                        $condition,
                        'Invalid'
                    );
                }
            }
        }
    }
}
