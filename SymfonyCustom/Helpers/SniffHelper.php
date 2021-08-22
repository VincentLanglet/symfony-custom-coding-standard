<?php

declare(strict_types=1);

namespace SymfonyCustom\Helpers;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;

use function mb_strtolower;
use function preg_match;
use function trim;

/**
 * Class SniffHelper
 */
class SniffHelper extends AbstractHelper
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
     * @param File $phpcsFile
     * @param int  $scopePtr
     *
     * @return array
     */
    public static function getUseStatements(File $phpcsFile, int $scopePtr): array
    {
        $tokens = $phpcsFile->getTokens();

        $uses = [];

        if (isset($tokens[$scopePtr]['scope_opener'])) {
            $start = $tokens[$scopePtr]['scope_opener'];
            $end = $tokens[$scopePtr]['scope_closer'];
        } else {
            $start = $scopePtr;
            $end = null;
        }

        $use = $phpcsFile->findNext(T_USE, $start + 1, $end);
        while (false !== $use && T_USE === $tokens[$use]['code']) {
            if (
                !self::isGlobalUse($phpcsFile, $use)
                || (null !== $end
                    && (!isset($tokens[$use]['conditions'][$scopePtr])
                        || $tokens[$use]['level'] !== $tokens[$scopePtr]['level'] + 1))
            ) {
                $use = $phpcsFile->findNext(Tokens::$emptyTokens, $use + 1, $end, true);
                continue;
            }

            // find semicolon as the end of the global use scope
            $endOfScope = $phpcsFile->findNext(T_SEMICOLON, $use + 1);

            $startOfName = $phpcsFile->findNext([T_STRING, T_NS_SEPARATOR], $use + 1, $endOfScope);

            $type = 'class';
            if (T_STRING === $tokens[$startOfName]['code']) {
                $lowerContent = mb_strtolower($tokens[$startOfName]['content']);
                if ('function' === $lowerContent || 'const' === $lowerContent) {
                    $type = $lowerContent;

                    $startOfName = $phpcsFile->findNext([T_STRING, T_NS_SEPARATOR], $startOfName + 1, $endOfScope);
                }
            }

            $uses[] = [
                'ptrUse' => $use,
                'name'   => trim($phpcsFile->getTokensAsString($startOfName, $endOfScope - $startOfName)),
                'ptrEnd' => $endOfScope,
                'string' => trim($phpcsFile->getTokensAsString($use, $endOfScope - $use + 1)),
                'type'   => $type,
            ];

            $use = $phpcsFile->findNext(Tokens::$emptyTokens, $endOfScope + 1, $end, true);
        }

        return $uses;
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
