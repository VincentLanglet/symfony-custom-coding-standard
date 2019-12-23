<?php

declare(strict_types=1);

namespace SymfonyCustom\Sniffs\NamingConventions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Throws errors if scalar type name are not valid.
 */
class ValidScalarTypeNameSniff implements Sniff
{
    /**
     * Types to replace: key is type to replace, value is type to replace with.
     *
     * @var array
     */
    public $types = [
        'boolean' => 'bool',
        'double'  => 'float',
        'integer' => 'int',
        'real'    => 'float',
    ];

    /**
     * @return array
     */
    public function register(): array
    {
        $tokens = Tokens::$castTokens;
        $tokens[] = T_DOC_COMMENT_OPEN_TAG;

        return $tokens;
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        if (T_DOC_COMMENT_OPEN_TAG === $tokens[$stackPtr]['code']) {
            $this->validateDocComment($phpcsFile, $stackPtr);
        } else {
            $this->validateCast($phpcsFile, $stackPtr);
        }
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    private function validateDocComment(File $phpcsFile, int $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        foreach ($tokens[$stackPtr]['comment_tags'] as $commentTag) {
            if (in_array(
                $tokens[$commentTag]['content'],
                ['@param', '@return', '@var']
            )
            ) {
                $docString = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $commentTag);
                if (false !== $docString) {
                    $stringParts = explode(' ', $tokens[$docString]['content']);
                    $typeName = $stringParts[0];
                    $this->validateTypeName($phpcsFile, $docString, $typeName);
                }
            }
        }
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    private function validateCast(File $phpcsFile, int $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        preg_match('/^\(\s*(\S+)\s*\)$/', $tokens[$stackPtr]['content'], $matches);
        $typeName = $matches[1];

        $this->validateTypeName($phpcsFile, $stackPtr, $typeName);
    }

    /**
     * @param File   $phpcsFile
     * @param int    $stackPtr
     * @param string $typeName
     */
    private function validateTypeName(File $phpcsFile, int $stackPtr, string $typeName): void
    {
        $validTypeName = $this->getValidTypeName($typeName);

        if (null !== $validTypeName) {
            $needFix = $phpcsFile->addFixableError(
                'For type-hinting in PHPDocs and casting, use %s instead of %s',
                $stackPtr,
                '',
                [$validTypeName, $typeName]
            );
            if ($needFix) {
                $tokens = $phpcsFile->getTokens();
                $phpcsFile->fixer->beginChangeset();
                $newContent = str_replace(
                    $typeName,
                    $validTypeName,
                    $tokens[$stackPtr]['content']
                );
                $phpcsFile->fixer->replaceToken($stackPtr, $newContent);
                $phpcsFile->fixer->endChangeset();
            }
        }
    }

    /**
     * @param string $typeName
     *
     * @return string|null
     */
    private function getValidTypeName(string $typeName): ?string
    {
        $typeName = strtolower($typeName);
        if (isset($this->types[$typeName])) {
            return $this->types[$typeName];
        }

        return null;
    }
}
