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
    private const TEXT = '[\\\\a-z0-9]';
    private const OPENER = '\<|\[|\{|\(';
    private const MIDDLE = '\,|\:|\=\>';
    private const CLOSER = '\>|\]|\}|\)';
    private const SEPARATOR = '\&|\|';

    /*
     * <simple> is any non-array, non-generic, non-alternated type, eg `int` or `\Foo`
     * <array> is array of <simple>, eg `int[]` or `\Foo[]`
     * <generic> is generic collection type, like `array<string, int>`, `Collection<Item>` and more complex like `Collection<int, \null|SubCollection<string>>`
     * <type> is <simple>, <array> or <generic> type, like `int`, `bool[]` or `Collection<ItemKey, ItemVal>`
     * <types> is one or more types alternated via `|`, like `int|bool[]|Collection<ItemKey, ItemVal>`
     */
    private const REGEX_TYPES = '
    (?<types>
        (?<type>
            (?<array>
                (?&simple)(\[\])*
            )
            |
            (?<simple>
                [@$?]?[\\\\\w]+
            )
            |
            (?<generic>
                (?<genericName>(?&simple))
                <
                    (?:(?<genericKey>(?&types)),\s*)?(?<genericValue>(?&types)|(?&generic))
                >
            )
        )
        (?:
            \|
            (?:(?&simple)|(?&array)|(?&generic))
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
            $endOfContent = preg_replace('/'.preg_quote($content, '/').'/', '', $tokens[$stackPtr + 2]['content'], 1);

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
     * @param string $content
     *
     * @return string
     */
    private function getValidTypes(string $content): string
    {
        $types = $this->getTypes($content);

        foreach ($types as $index => $type) {
            $type = str_replace(' ', '', $type);

            preg_match('{^'.self::REGEX_TYPES.'$}x', $type, $matches);
            if (isset($matches['generic'])) {
                $validType = $this->getValidType($matches['genericName']).'<';

                if ('' !== $matches['genericKey']) {
                    $validType .= $this->getValidTypes($matches['genericKey']).', ';
                }

                $validType .= $this->getValidTypes($matches['genericValue']).'>';
            } else {
                $validType = $this->getValidType($type);
            }

            $types[$index] = $validType;
        }

        return implode('|', $types);
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
