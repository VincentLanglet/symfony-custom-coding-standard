<?php

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
    public function register()
    {
        return [T_USE];
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     *
     * @return int|void
     */
    public function process(File $phpcsFile, $stackPtr)
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
            $error = 'Use statement cannot start with a backslash';
            $fix = $phpcsFile->addFixableError($error, $classPtr, 'BackslashAtStart');

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
