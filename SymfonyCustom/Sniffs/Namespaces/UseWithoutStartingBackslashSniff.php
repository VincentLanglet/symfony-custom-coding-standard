<?php

declare(strict_types=1);

namespace SymfonyCustom\Sniffs\Namespaces;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;
use SymfonyCustom\Sniffs\SniffHelper;

/**
 * Class UseWithoutStartingBackslashSniff
 */
class UseWithoutStartingBackslashSniff implements Sniff
{
    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_USE];
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        if (!SniffHelper::isGlobalUse($phpcsFile, $stackPtr)) {
            return;
        }

        $tokens = $phpcsFile->getTokens();
        $classPtr = $phpcsFile->findNext(
            Tokens::$emptyTokens,
            $stackPtr + 1,
            null,
            true
        );

        $lowerContent = strtolower($tokens[$classPtr]['content']);
        if ('function' === $lowerContent || 'const' === $lowerContent) {
            $classPtr = $phpcsFile->findNext(
                Tokens::$emptyTokens,
                $classPtr + 1,
                null,
                true
            );
        }

        if (T_NS_SEPARATOR === $tokens[$classPtr]['code']
            || (T_STRING === $tokens[$classPtr]['code']
                && '\\' === $tokens[$classPtr]['content'])
        ) {
            $fix = $phpcsFile->addFixableError(
                'Use statement cannot start with a backslash',
                $classPtr,
                'BackslashAtStart'
            );

            if ($fix) {
                if (T_WHITESPACE !== $tokens[$classPtr - 1]['code']) {
                    $phpcsFile->fixer->replaceToken($classPtr, ' ');
                } else {
                    $phpcsFile->fixer->replaceToken($classPtr, '');
                }
            }
        }
    }
}
