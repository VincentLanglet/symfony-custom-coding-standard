<?php

declare(strict_types=1);

namespace SymfonyCustom\Helpers;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Class SniffHelper
 */
class SniffHelper extends AbstractHelper
{
    public const TAGS = [
        '@required', // Symfony

        '@filesource',
        '@source',
        '@category',
        '@package',
        '@subpackage',
        '@author',
        '@created',
        '@copyright',
        '@license',
        '@version',
        '@since',

        '@api',
        '@internal',
        '@deprecated',
        '@ignore',
        '@todo',

        '@example',
        '@link',
        '@see',

        '@global',
        '@name',
        '@property',
        '@property-read',
        '@property-write',
        '@method',
        '@uses',

        '@var',
        '@param',
        '@return',
        '@throws',

        '@abstract',
        '@final',
        '@public',
        '@protected',
        '@private',
        '@static',
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
     * <simple> is any non-array, non-generic, non-alternated type, eg `int` or `\Foo`
     * <array> is array of <simple>, eg `int[]` or `\Foo[]`
     * <generic> is generic collection type, like `array<string, int>`, `Collection<Item>` or more complex`
     * <object> is array key => value type, like `array{type: string, name: string, value: mixed}`
     * <type> is <simple>, <array>, <object>, <generic> type
     * <types> is one or more types alternated via `|`, like `int|bool[]|Collection<ItemKey, ItemVal>`
     */
    public const REGEX_TYPES = '
    (?<types>
        (?<type>
            (?<array>
                (?&notArray)(?:
                    \s*\[\s*\]
                )+
            )
            |
            (?<notArray>
                (?<multiple>
                    \(\s*(?<mutipleContent>
                        (?&types)
                    )\s*\)
                )
                |
                (?<generic>
                    (?<genericName>
                        (?&simple)
                    )
                    \s*<\s*
                        (?<genericContent>
                            (?:(?&types)\s*,\s*)*
                            (?&types)
                        )
                    \s*>
                )
                |
                (?<object>
                    array\s*{\s*
                        (?<objectContent>
                            (?:
                                (?<objectKeyValue>
                                    (?:\w+\s*\??:\s*)?
                                    (?&types)
                                )
                                \s*,\s*
                            )*
                            (?&objectKeyValue)
                        )
                    \s*}
                )
                |
                (?<simple>
                    \\\\?\w+(?:\\\\\w+)*
                    |
                    \$this
                )
            )
        )
        (?:
            \s*[\|&]\s*(?&type)
        )*
    )
    ';

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

    /**
     * @param string $content
     *
     * @return array
     */
    public static function parseTypeHint(string $content): array
    {
        preg_match(
            '{^'.self::REGEX_TYPES.'(?<space>[\s\t]*)?(?<description>.*)?$}six',
            $content,
            $matches
        );

        return [$matches['types'] ?? '', $matches['space'] ?? '', $matches['description'] ?? ''];
    }
}
