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
        return [T_CLASS];
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

        $end = null;
        if (isset($tokens[$stackPtr]['scope_closer'])) {
            $end = $tokens[$stackPtr]['scope_closer'];
        }

        $scope = $phpcsFile->findNext(T_FUNCTION, $stackPtr, $end);

        $wantedTokens = [T_PUBLIC, T_PROTECTED, T_PRIVATE];

        while ($scope) {
            $scope = $phpcsFile->findNext($wantedTokens, $scope + 1, $end);

            if ($scope && T_VARIABLE === $tokens[$scope + 2]['code']) {
                $phpcsFile->addError('Declare class properties before methods', $scope, 'Invalid');
            }
        }
    }
}
