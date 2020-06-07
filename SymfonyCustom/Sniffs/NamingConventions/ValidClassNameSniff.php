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
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $line = $tokens[$stackPtr]['line'];

        while ($tokens[$stackPtr]['line'] === $line) {
            // Suffix interfaces with Interface
            if (T_INTERFACE === $tokens[$stackPtr]['code']) {
                $name = $phpcsFile->findNext(T_STRING, $stackPtr);

                $this->checkSuffix($phpcsFile, $stackPtr, $name, 'Interface');
                break;
            }

            // Suffix traits with Trait
            if (T_TRAIT === $tokens[$stackPtr]['code']) {
                $name = $phpcsFile->findNext(T_STRING, $stackPtr);

                $this->checkSuffix($phpcsFile, $stackPtr, $name, 'Trait');
                break;
            }

            // Suffix exceptions with Exception;
            if (T_EXTENDS === $tokens[$stackPtr]['code']) {
                $extend = $phpcsFile->findNext(T_STRING, $stackPtr);

                if ($extend && 'Exception' === substr($tokens[$extend]['content'], -9)) {
                    $class = $phpcsFile->findPrevious(T_CLASS, $stackPtr);
                    $name = $phpcsFile->findNext(T_STRING, $class);

                    $this->checkSuffix($phpcsFile, $stackPtr, $name, 'Exception');
                }
                break;
            }

            // Prefix abstract classes with Abstract.
            if (T_ABSTRACT === $tokens[$stackPtr]['code']) {
                $name = $phpcsFile->findNext([T_STRING, T_FUNCTION], $stackPtr);

                // Making sure we're not dealing with an abstract function
                if (false !== $name && T_FUNCTION !== $tokens[$name]['code']) {
                    $this->checkPrefix($phpcsFile, $stackPtr, $name, 'Abstract');
                }
                break;
            }

            $stackPtr++;
        }
    }

    /**
     * @param File     $phpcsFile
     * @param int      $stackPtr
     * @param int|bool $name
     * @param string   $prefix
     *
     * @return void
     */
    private function checkPrefix(File $phpcsFile, int $stackPtr, $name, string $prefix): void
    {
        $tokens = $phpcsFile->getTokens();

        if (false !== $name && substr($tokens[$name]['content'], 0, strlen($prefix)) !== $prefix) {
            $phpcsFile->addError(
                "$prefix name is not prefixed with '$prefix'",
                $stackPtr,
                "Invalid{$prefix}Name"
            );
        }
    }

    /**
     * @param File     $phpcsFile
     * @param int      $stackPtr
     * @param int|bool $name
     * @param string   $suffix
     *
     * @return void
     */
    private function checkSuffix(File $phpcsFile, int $stackPtr, $name, string $suffix): void
    {
        $tokens = $phpcsFile->getTokens();

        if (false !== $name && substr($tokens[$name]['content'], -strlen($suffix)) !== $suffix) {
            $phpcsFile->addError(
                "$suffix name is not suffixed with '$suffix'",
                $stackPtr,
                "Invalid{$suffix}Name"
            );
        }
    }
}
