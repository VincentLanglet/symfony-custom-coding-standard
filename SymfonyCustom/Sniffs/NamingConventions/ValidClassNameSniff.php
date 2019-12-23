<?php

declare(strict_types=1);

namespace SymfonyCustom\Sniffs\NamingConventions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Throws errors if symfony's naming conventions are not met.
 */
class ValidClassNameSniff implements Sniff
{
    /**
     * @return int[]
     */
    public function register()
    {
        return [
            T_INTERFACE,
            T_TRAIT,
            T_EXTENDS,
            T_ABSTRACT,
        ];
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $line = $tokens[$stackPtr]['line'];

        while ($tokens[$stackPtr]['line'] === $line) {
            // Suffix interfaces with Interface
            if (T_INTERFACE === $tokens[$stackPtr]['code']) {
                $name = $phpcsFile->findNext(T_STRING, $stackPtr);

                if ($name && substr($tokens[$name]['content'], -9) !== 'Interface') {
                    $phpcsFile->addError(
                        'Interface name is not suffixed with "Interface"',
                        $stackPtr,
                        'InvalidInterfaceName'
                    );
                }
                break;
            }

            // Suffix traits with Trait
            if (T_TRAIT === $tokens[$stackPtr]['code']) {
                $name = $phpcsFile->findNext(T_STRING, $stackPtr);

                if ($name && substr($tokens[$name]['content'], -5) !== 'Trait') {
                    $phpcsFile->addError(
                        'Trait name is not suffixed with "Trait"',
                        $stackPtr,
                        'InvalidTraitName'
                    );
                }
                break;
            }

            // Suffix exceptions with Exception;
            if (T_EXTENDS === $tokens[$stackPtr]['code']) {
                $extend = $phpcsFile->findNext(T_STRING, $stackPtr);

                if ($extend
                    && substr($tokens[$extend]['content'], -9) === 'Exception'
                ) {
                    $class = $phpcsFile->findPrevious(T_CLASS, $stackPtr);
                    $name = $phpcsFile->findNext(T_STRING, $class);

                    if ($name
                        && substr($tokens[$name]['content'], -9) !== 'Exception'
                    ) {
                        $phpcsFile->addError(
                            'Exception name is not suffixed with "Exception"',
                            $stackPtr,
                            'InvalidExceptionName'
                        );
                    }
                }
                break;
            }

            // Prefix abstract classes with Abstract.
            if (T_ABSTRACT === $tokens[$stackPtr]['code']) {
                $name = $phpcsFile->findNext(T_STRING, $stackPtr);
                $function = $phpcsFile->findNext(T_FUNCTION, $stackPtr);

                // Making sure we're not dealing with an abstract function
                if ($name && (false === $function || $name < $function)
                    && substr($tokens[$name]['content'], 0, 8) !== 'Abstract'
                ) {
                    $phpcsFile->addError(
                        'Abstract class name is not prefixed with "Abstract"',
                        $stackPtr,
                        'InvalidAbstractName'
                    );
                }
                break;
            }

            $stackPtr++;
        }
    }
}
