<?php

declare(strict_types=1);

namespace SymfonyCustom\Sniffs\NamingConventions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Common;
use SymfonyCustom\Helpers\SniffHelper;

/**
 * Throws errors if scalar type name are not valid.
 */
class ValidScalarTypeNameSniff implements Sniff
{
    private const TYPING = '\\\\a-z0-9';
    private const OPENER = '\<\[\{\(';
    private const CLOSER = '\>\]\}\)';
    private const MIDDLE = '\,\:\&\|';

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
            preg_match(
                '`^((?:'
                    .'['.self::OPENER.self::MIDDLE.']\s*'
                    .'|(?:['.self::TYPING.self::CLOSER.']\s+)(?=[\|'.self::OPENER.self::MIDDLE.self::CLOSER.'])'
                    .'|['.self::TYPING.self::CLOSER.']'
                .')+)(.*)?`i',
                $tokens[($stackPtr + 2)]['content'],
                $match
            );

            if (isset($match[1]) === false) {
                return;
            }

            // Check type (can be multiple, separated by '|').
            $type = $match[1];
            $suggestedType = $this->getValidTypeName($type);
            if ($type !== $suggestedType) {
                $fix = $phpcsFile->addFixableError(
                    'For type-hinting in PHPDocs, use %s instead of %s',
                    $stackPtr + 2,
                    'Invalid',
                    [$suggestedType, $type]
                );

                if ($fix) {
                    $replacement = $suggestedType;
                    if (isset($match[2])) {
                        $replacement .= $match[2];
                    }

                    $phpcsFile->fixer->replaceToken($stackPtr + 2, $replacement);
                }
            }
        }
    }

    /**
     * @param string $typeName
     *
     * @return string
     */
    private function getValidTypeName(string $typeName): string
    {
        $typeNameWithoutSpace = str_replace(' ', '', $typeName);
        $parts = preg_split(
            '/([\|'.self::OPENER.self::MIDDLE.self::CLOSER.'])/',
            $typeNameWithoutSpace,
            -1,
            PREG_SPLIT_DELIM_CAPTURE
        );
        $partsNumber = count($parts) - 1;

        $validType = '';
        for ($i = 0; $i < $partsNumber; $i += 2) {
            $validType .= $this->suggestType($parts[$i]).$parts[$i + 1];

            if (in_array($parts[$i + 1], [',', ':'])) {
                $validType .= ' ';
            }
        }

        if ('' !== $parts[$partsNumber]) {
            $validType .= $this->suggestType($parts[$partsNumber]);
        }

        return $validType;
    }

    /**
     * @param string $typeName
     *
     * @return string
     */
    private function suggestType(string $typeName): string
    {
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
