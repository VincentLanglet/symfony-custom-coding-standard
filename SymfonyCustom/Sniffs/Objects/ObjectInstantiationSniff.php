<?php

namespace SymfonyCustom\Sniffs\Objects;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Throws a warning if an object isn't instantiated using parenthesis.
 */
class ObjectInstantiationSniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [
            T_NEW,
        ];
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $allowed = [
            T_STRING,
            T_NS_SEPARATOR,
            T_VARIABLE,
            T_STATIC,
            T_SELF,
        ];

        $object = $stackPtr;
        $line   = $tokens[$object]['line'];

        while ($object && $tokens[$object]['line'] === $line) {
            $object = $phpcsFile->findNext($allowed, $object + 1);

            if ($tokens[$object]['line'] === $line
                && !in_array($tokens[$object + 1]['code'], $allowed)
            ) {
                if (T_OPEN_PARENTHESIS !== $tokens[$object + 1]['code']) {
                    $phpcsFile->addError(
                        'Use parentheses when instantiating classes',
                        $stackPtr,
                        'Invalid'
                    );
                }

                break;
            }
        }
    }
}
