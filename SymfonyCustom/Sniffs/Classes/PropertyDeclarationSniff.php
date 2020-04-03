<?php

declare(strict_types=1);

namespace SymfonyCustom\Sniffs\Classes;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Throws error if properties are declared after methods
 */
class PropertyDeclarationSniff implements Sniff
{
    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_CLASS, T_ANON_CLASS];
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $this->processProperty($phpcsFile, $stackPtr);
        $this->processFunction($phpcsFile, $stackPtr);
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     *
     * @return void
     */
    private function processFunction(File $phpcsFile, int $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $end = $tokens[$stackPtr]['scope_closer'] ?? null;

        $function = $phpcsFile->findNext(T_FUNCTION, $stackPtr, $end);
        if (false === $function) {
            return;
        }

        $wantedTokens = [T_CONST, T_PUBLIC, T_PROTECTED, T_PRIVATE, T_ANON_CLASS];
        $scope = $phpcsFile->findNext($wantedTokens, $function + 1, $end);

        while (false !== $scope) {
            if (T_ANON_CLASS === $tokens[$scope]['code']) {
                $scope = $tokens[$scope]['scope_closer'];

                continue;
            }

            if (T_CONST === $tokens[$scope]['code']) {
                $phpcsFile->addError('Declare class constants before methods', $scope, 'ConstBeforeFunction');
            } elseif (T_VARIABLE === $tokens[$scope + 2]['code']) {
                $phpcsFile->addError('Declare class properties before methods', $scope, 'PropertyBeforeFunction');
            }

            $scope = $phpcsFile->findNext($wantedTokens, $scope + 1, $end);
        }
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     *
     * @return void
     */
    private function processProperty(File $phpcsFile, int $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $end = $tokens[$stackPtr]['scope_closer'] ?? null;

        $wantedTokens = [T_PUBLIC, T_PROTECTED, T_PRIVATE, T_ANON_CLASS];
        $scope = $phpcsFile->findNext($wantedTokens, $stackPtr + 1, $end);

        while (false !== $scope && T_VARIABLE !== $tokens[$scope + 2]['code']) {
            if (T_ANON_CLASS === $tokens[$scope]['code']) {
                $scope = $tokens[$scope]['scope_closer'];

                continue;
            }

            $scope = $phpcsFile->findNext($wantedTokens, $scope + 1, $end);
        }

        if (false === $scope) {
            return;
        }
        $property = $scope + 2;

        $wantedTokens = [T_CONST, T_ANON_CLASS];
        $scope = $phpcsFile->findNext($wantedTokens, $property + 1, $end);

        while (false !== $scope) {
            if (T_ANON_CLASS === $tokens[$scope]['code']) {
                $scope = $tokens[$scope]['scope_closer'];

                continue;
            }

            if (T_CONST === $tokens[$scope]['code']) {
                $phpcsFile->addError('Declare class property before const', $scope, 'ConstBeforeProperty');
            }

            $scope = $phpcsFile->findNext($wantedTokens, $scope + 1, $end);
        }
    }
}
