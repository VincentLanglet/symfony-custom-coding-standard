<?php

declare(strict_types=1);

namespace SymfonyCustom\Sniffs\NamingConventions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Common;
use SymfonyCustom\Helpers\SniffHelper;

/**
 * Throws errors if PHPDocs type hint are not valid.
 */
class ValidTypeHintSniff implements Sniff
{
    /**
     * <simple> is any non-array, non-generic, non-alternated type, eg `int` or `\Foo`
     * <array> is array of <simple>, eg `int[]` or `\Foo[]`
     * <generic> is generic collection type, like `array<string, int>`, `Collection<Item>` or more complex`
     * <object> is array key => value type, like `array{type: string, name: string, value: mixed}`
     * <class-string> is Foo::class type, like `class-string` or `class-string<Foo>`
     * <type> is <simple>, <class-string>, <array>, <object> or <generic> type
     * <types> is one or more types alternated via `|`, like `int|bool[]|Collection<ItemKey, ItemVal>`
     */
    private const REGEX_TYPES = '
    (?<types>
        (?<type>
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
            (?<array>
                (?&simple)(?:
                    \s*\[\s*\]
                )+
            )
            |
            (?<classString>
                class-string(?:
                    \s*<\s*[\\\\\w]+\s*>
                )?
            )
            |
            (?<simple>
                [@$?]?[\\\\\w]+
            )
        )
        (?:
            \s*\|\s*(?&type)
        )*
    )
    ';

    /**
     * @return array
     */
    public function register(): array
    {
        return [T_DOC_COMMENT_TAG];
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();

        if (in_array($tokens[$stackPtr]['content'], SniffHelper::TAGS_WITH_TYPE)) {
            $matchingResult = preg_match(
                '{^'.self::REGEX_TYPES.'(?:[ \t].*)?$}sx',
                $tokens[$stackPtr + 2]['content'],
                $matches
            );

            $content = 1 === $matchingResult ? $matches['types'] : '';
            $endOfContent = substr($tokens[$stackPtr + 2]['content'], strlen($content));

            $suggestedType = $this->getValidTypes($content);

            if ($content !== $suggestedType) {
                $fix = $phpcsFile->addFixableError(
                    'For type-hinting in PHPDocs, use %s instead of %s',
                    $stackPtr + 2,
                    'Invalid',
                    [$suggestedType, $content]
                );

                if ($fix) {
                    $phpcsFile->fixer->replaceToken($stackPtr + 2, $suggestedType.$endOfContent);
                }
            }
        }
    }

    /**
     * @param string $content
     *
     * @return string
     */
    private function getValidTypes(string $content): string
    {
        $content = preg_replace('/\s/', '', $content);
        $types = $this->getTypes($content);

        foreach ($types as $index => $type) {
            preg_match('{^'.self::REGEX_TYPES.'$}x', $type, $matches);

            if (isset($matches['generic']) && '' !== $matches['generic']) {
                $validType = $this->getValidGenericType($matches['genericName'], $matches['genericContent']);
            } elseif (isset($matches['object']) && '' !== $matches['object']) {
                $validType = $this->getValidObjectType($matches['objectContent']);
            } else {
                $validType = $this->getValidType($type);
            }

            $types[$index] = $validType;
        }

        $types = array_unique($types);
        usort($types, function ($type1, $type2) {
            if ('null' === $type1) {
                return 1;
            }

            if ('null' === $type2) {
                return -1;
            }

            return 0;
        });

        return implode('|', $types);
    }

    /**
     * @param string $content
     *
     * @return array
     */
    private function getTypes(string $content): array
    {
        $types = [];
        while ('' !== $content && false !== $content) {
            preg_match('{^'.self::REGEX_TYPES.'$}x', $content, $matches);

            $types[] = $matches['type'];
            $content = substr($content, strlen($matches['type']) + 1);
        }

        return $types;
    }

    /**
     * @param string $genericName
     * @param string $genericContent
     *
     * @return string
     */
    private function getValidGenericType(string $genericName, string $genericContent): string
    {
        $validType = $this->getValidType($genericName).'<';

        while ('' !== $genericContent && false !== $genericContent) {
            preg_match('{^'.self::REGEX_TYPES.',?}x', $genericContent, $matches);

            $validType .= $this->getValidTypes($matches['types']).', ';
            $genericContent = substr($genericContent, strlen($matches['types']) + 1);
        }

        return preg_replace('/,\s$/', '>', $validType);
    }

    /**
     * @param string $objectContent
     *
     * @return string
     */
    private function getValidObjectType(string $objectContent): string
    {
        $validType = 'array{';

        while ('' !== $objectContent && false !== $objectContent) {
            $split = preg_split('/(\??:|,)/', $objectContent, 2, PREG_SPLIT_DELIM_CAPTURE);

            if (isset($split[1]) && ',' !== $split[1]) {
                $validType .= $split[0].$split[1].' ';
                $objectContent = $split[2];
            }

            preg_match('{^'.self::REGEX_TYPES.',?}x', $objectContent, $matches);

            $validType .= $this->getValidTypes($matches['types']).', ';
            $objectContent = substr($objectContent, strlen($matches['types']) + 1);
        }

        return preg_replace('/,\s$/', '}', $validType);
    }

    /**
     * @param string $typeName
     *
     * @return string
     */
    private function getValidType(string $typeName): string
    {
        if ('[]' === substr($typeName, -2)) {
            return $this->getValidType(substr($typeName, 0, -2)).'[]';
        }

        $lowerType = strtolower($typeName);
        switch ($lowerType) {
            case 'bool':
            case 'boolean':
                return 'bool';
            case 'int':
            case 'integer':
                return 'int';
        }

        return Common::suggestType($typeName);
    }
}
