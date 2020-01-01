<?php

declare(strict_types=1);

namespace SymfonyCustom\Sniffs\NamingConventions;

use PHP_CodeSniffer\Exceptions\DeepExitException;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
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
                (?<classString>
                    class-string(?:
                        \s*<\s*
                        (?<classStringContent>
                            (?&simple)
                        )
                        \s*>
                    )?
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

    private const PRIMITIVES_TYPES = [
        'string',
        'int',
        'float',
        'bool',
        'array',
        'resource',
        'null',
        'callable',
        'iterable',
    ];
    private const KEYWORD_TYPES = [
        'mixed',
        'void',
        'object',
        'number',
        'false',
        'true',
        'self',
        'static',
        '$this',
    ];
    private const ALIAS_TYPES = [
        'boolean'  => 'bool',
        'integer'  => 'int',
        'double'   => 'float',
        'real'     => 'float',
        'callback' => 'callable',
    ];

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

        if (!in_array($tokens[$stackPtr]['content'], SniffHelper::TAGS_WITH_TYPE)) {
            return;
        }

        $matchingResult = preg_match(
            '{^'.self::REGEX_TYPES.'(?:[\s\t].*)?$}six',
            $tokens[$stackPtr + 2]['content'],
            $matches
        );

        $content = 1 === $matchingResult ? $matches['types'] : '';
        $endOfContent = substr($tokens[$stackPtr + 2]['content'], strlen($content));

        try {
            $suggestedType = $this->getValidTypes($content);
        } catch (DeepExitException $exception) {
            $phpcsFile->addError(
                $exception->getMessage(),
                $stackPtr + 2,
                'Exception'
            );

            return;
        }

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

    /**
     * @param string $content
     *
     * @return string
     *
     * @throws DeepExitException
     */
    private function getValidTypes(string $content): string
    {
        $content = preg_replace('/\s/', '', $content);

        $types = [];
        $separators = [];
        while ('' !== $content && false !== $content) {
            preg_match('{^'.self::REGEX_TYPES.'$}ix', $content, $matches);

            if (isset($matches['array']) && '' !== $matches['array']) {
                $validType = $this->getValidTypes(substr($matches['array'], 0, -2)).'[]';
            } elseif (isset($matches['multiple']) && '' !== $matches['multiple']) {
                $validType = '('.$this->getValidTypes($matches['mutipleContent']).')';
            } elseif (isset($matches['generic']) && '' !== $matches['generic']) {
                $validType = $this->getValidGenericType($matches['genericName'], $matches['genericContent']);
            } elseif (isset($matches['object']) && '' !== $matches['object']) {
                $validType = $this->getValidObjectType($matches['objectContent']);
            } elseif (isset($matches['classString']) && '' !== $matches['classString']) {
                $validType = preg_replace('/class-string/i', 'class-string', $matches['classString']);
            } else {
                $validType = $this->getValidType($matches['type']);
            }

            $types[] = $validType;

            $separators[] = substr($content, strlen($matches['type']), 1);
            $content = substr($content, strlen($matches['type']) + 1);
        }

        // Remove last separator since it's an empty string
        array_pop($separators);

        $uniqueSeparators = array_unique($separators);
        switch (count($uniqueSeparators)) {
            case 0:
                return implode('', $types);
            case 1:
                return implode($uniqueSeparators[0], $this->orderTypes($types));
            default:
                throw new DeepExitException(
                    'Union and intersection types must be grouped with parenthesis when used in the same expression'
                );
        }
    }

    /**
     * @param array $types
     *
     * @return array
     */
    private function orderTypes(array $types): array
    {
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

        return $types;
    }

    /**
     * @param string $genericName
     * @param string $genericContent
     *
     * @return string
     *
     * @throws DeepExitException
     */
    private function getValidGenericType(string $genericName, string $genericContent): string
    {
        $validType = $this->getValidType($genericName).'<';

        while ('' !== $genericContent && false !== $genericContent) {
            preg_match('{^'.self::REGEX_TYPES.',?}ix', $genericContent, $matches);

            $validType .= $this->getValidTypes($matches['types']).', ';
            $genericContent = substr($genericContent, strlen($matches['types']) + 1);
        }

        return preg_replace('/,\s$/', '>', $validType);
    }

    /**
     * @param string $objectContent
     *
     * @return string
     *
     * @throws DeepExitException
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

            preg_match('{^'.self::REGEX_TYPES.',?}ix', $objectContent, $matches);

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
        $lowerType = strtolower($typeName);
        if (in_array($lowerType, self::PRIMITIVES_TYPES) || in_array($lowerType, self::KEYWORD_TYPES)) {
            return $lowerType;
        }

        if (isset(self::ALIAS_TYPES[$lowerType])) {
            return self::ALIAS_TYPES[$lowerType];
        }

        return $typeName;
    }
}
