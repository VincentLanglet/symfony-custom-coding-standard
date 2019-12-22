<?php

namespace SymfonyCustom\Sniffs\Formatting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Throws error if == or != are use
 */
class StrictComparisonSniff implements Sniff
{
    /**
     * Types to replace: key is operator to replace, value is operator to replace with.
     *
     * @var array
     */
    private $operators = [
        T_IS_EQUAL     => '===',
        T_IS_NOT_EQUAL => '!==',
    ];

    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_IS_EQUAL, T_IS_NOT_EQUAL];
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();

        // This error is fixable, but it's too dangerous to add automatically fixer
        $phpcsFile->addError(
            'The %s comparator is forbidden, use %s instead',
            $stackPtr,
            'NotStrict',
            [
                $tokens[$stackPtr]['content'],
                $this->operators[$tokens[$stackPtr]['code']],
            ]
        );
    }
}
