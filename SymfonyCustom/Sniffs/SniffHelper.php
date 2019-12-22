<?php

namespace SymfonyCustom\Sniffs;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;

/**
 * class Helper
 *
 * @internal
 */
class SniffHelper
{
    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     *
     * @return bool
     */
    public static function isNamespace(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if (T_NAMESPACE !== $tokens[$stackPtr]['code']) {
            return false;
        }

        $nextNonEmpty = $phpcsFile->findNext(Tokens::$emptyTokens, ($stackPtr + 1), null, true);

        return false === $nextNonEmpty || T_NS_SEPARATOR !== $tokens[$nextNonEmpty]['code'];
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     *
     * @return bool
     */
    public static function isTraitUse(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Ignore USE keywords inside closures.
        $next = $phpcsFile->findNext(Tokens::$emptyTokens, $stackPtr + 1, null, true);
        if (T_OPEN_PARENTHESIS === $tokens[$next]['code']) {
            return false;
        }

        // Ignore global USE keywords.
        if (!$phpcsFile->hasCondition($stackPtr, [T_CLASS, T_TRAIT, T_ANON_CLASS])) {
            return false;
        }

        return true;
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     *
     * @return bool
     */
    public static function isGlobalUse(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Ignore USE keywords inside closures.
        $next = $phpcsFile->findNext(Tokens::$emptyTokens, $stackPtr + 1, null, true);
        if (T_OPEN_PARENTHESIS === $tokens[$next]['code']) {
            return false;
        }

        // Ignore USE keywords for traits.
        if ($phpcsFile->hasCondition($stackPtr, [T_CLASS, T_TRAIT, T_ANON_CLASS])) {
            return false;
        }

        return true;
    }
}
