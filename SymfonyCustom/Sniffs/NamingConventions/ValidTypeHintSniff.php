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
                    .'(?:'.self::OPENER.'|'.self::MIDDLE.'|'.self::SEPARATOR.')\s+'
                        .'(?='.self::TEXT.'|'.self::OPENER.'|'.self::MIDDLE.'|'.self::CLOSER.'|'.self::SEPARATOR.')'
                    .'|'.self::OPENER.'|'.self::MIDDLE.'|'.self::SEPARATOR
                    .'|(?:'.self::TEXT.'|'.self::CLOSER.')\s+'
                        .'(?='.self::OPENER.'|'.self::MIDDLE.'|'.self::CLOSER.'|'.self::SEPARATOR.')'
                    .'|'.self::TEXT.'|'.self::CLOSER.''
                .')+)(.*)?`i',
                $tokens[($stackPtr + 2)]['content'],
                $match
            );

            if (isset($match[1]) === false) {
                return;
            }

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
            '/('.self::OPENER.'|'.self::MIDDLE.'|'.self::CLOSER.'|'.self::SEPARATOR.')/',
            $typeNameWithoutSpace,
            -1,
            PREG_SPLIT_DELIM_CAPTURE
        );
        $partsNumber = count($parts) - 1;

        $validType = '';
        for ($i = 0; $i < $partsNumber; $i += 2) {
            $validType .= $this->suggestType($parts[$i]);

            if ('=>' === $parts[$i + 1]) {
                $validType .= ' ';
            }

            $validType .= $parts[$i + 1];

            if (preg_match('/'.self::MIDDLE.'/', $parts[$i + 1])) {
                $validType .= ' ';
            }
        }

        if ('' !== $parts[$partsNumber]) {
            $validType .= $this->suggestType($parts[$partsNumber]);
        }

        return trim($validType);
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
