<?php

declare(strict_types=1);

namespace SymfonyCustom\Sniffs\Functions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

use function array_keys;
use function in_array;

/**
 * Throws warnings if properties are declared after methods
 */
class ScopeOrderSniff implements Sniff
{
    /**
     * @var array
     */
    public $whitelisted = ['__construct', 'setUp', 'tearDown'];

    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_CLASS, T_INTERFACE, T_ANON_CLASS];
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
        $function = $stackPtr;

        $scopes = [
            0 => T_PUBLIC,
            1 => T_PROTECTED,
            2 => T_PRIVATE,
        ];

        $end = null;
        if (isset($tokens[$stackPtr]['scope_closer'])) {
            $end = $tokens[$stackPtr]['scope_closer'];
        }

        while ($function) {
            $function = $phpcsFile->findNext(
                [T_FUNCTION, T_ANON_CLASS],
                $function + 1,
                $end
            );

            if (T_ANON_CLASS === $tokens[$function]['code']) {
                $function = $tokens[$function]['scope_closer'];

                continue;
            }

            if (isset($tokens[$function]['parenthesis_opener'])) {
                $scope = $phpcsFile->findPrevious($scopes, $function - 1, $stackPtr);
                $name = $phpcsFile->findNext(
                    T_STRING,
                    $function + 1,
                    $tokens[$function]['parenthesis_opener']
                );

                if (
                    $scope
                    && $name
                    && !in_array(
                        $tokens[$name]['content'],
                        $this->whitelisted
                    )
                ) {
                    $current = array_keys($scopes, $tokens[$scope]['code']);
                    $current = $current[0];

                    $error = 'Declare public methods first, then protected ones and finally private ones';

                    if (isset($previous) && $current < $previous) {
                        $phpcsFile->addError(
                            $error,
                            $scope,
                            'Invalid'
                        );
                    }

                    $previous = $current;
                }
            }
        }
    }
}
