<?php

declare(strict_types=1);

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
    public const TAGS = [
        '@api',
        '@author',
        '@category',
        '@copyright',
        '@covers',
        '@dataProvider',
        '@deprecated',
        '@example',
        '@filesource',
        '@global',
        '@ignore',
        '@internal',
        '@license',
        '@link',
        '@method',
        '@package',
        '@param',
        '@property',
        '@property-read',
        '@property-write',
        '@return',
        '@see',
        '@since',
        '@source',
        '@subpackage',
        '@throws',
        '@todo',
        '@uses',
        '@var',
        '@version',
    ];

    public const TAGS_WITH_TYPE = [
        '@method',
        '@param',
        '@property',
        '@property-read',
        '@property-write',
        '@return',
        '@throws',
        '@var',
    ];

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     *
     * @return bool
     */
    public static function isNamespace(File $phpcsFile, int $stackPtr): bool
    {
        $tokens = $phpcsFile->getTokens();

        if (T_NAMESPACE !== $tokens[$stackPtr]['code']) {
            return false;
        }

        $nextNonEmpty = $phpcsFile->findNext(Tokens::$emptyTokens, $stackPtr + 1, null, true);

        return !$nextNonEmpty || T_NS_SEPARATOR !== $tokens[$nextNonEmpty]['code'];
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     *
     * @return bool
     */
    public static function isTraitUse(File $phpcsFile, int $stackPtr): bool
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
    public static function isGlobalUse(File $phpcsFile, int $stackPtr): bool
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
