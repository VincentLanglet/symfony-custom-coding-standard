<?php

namespace SymfonyCustom\Sniffs\Objects;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Throws an error if an object isn't instantiated using parenthesis.
 */
class ObjectInstantiationSniff implements Sniff
{
    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_NEW];
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $allowed = [
            T_STRING,
            T_NS_SEPARATOR,
            T_VARIABLE,
            T_STATIC,
            T_SELF,
            T_OPEN_SQUARE_BRACKET,
            T_CLOSE_SQUARE_BRACKET,
        ];

        $object = $stackPtr;
        $line = $tokens[$object]['line'];

        while ($object && $tokens[$object]['line'] === $line) {
            $object = $phpcsFile->findNext($allowed, $object + 1);

            if ($tokens[$object]['line'] === $line && !in_array($tokens[$object + 1]['code'], $allowed)) {
                if (T_OPEN_PARENTHESIS !== $tokens[$object + 1]['code']) {
                    $phpcsFile->addError('Use parentheses when instantiating classes', $stackPtr, 'Invalid');
                }

                break;
            }
        }
    }
}
