<?php

declare(strict_types=1);

namespace SymfonyCustom\Sniffs\PHP;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SymfonyCustom\Helpers\SniffHelper;

use function array_filter;
use function array_map;
use function in_array;
use function mb_strtolower;

/**
 * Class ImportInternalFunctionSniff
 */
class ImportInternalFunctionSniff implements Sniff
{
    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_NAMESPACE];
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

        $uses = SniffHelper::getUseStatements($phpcsFile, $stackPtr);
        $functionUses = array_map(
            function (array $use): string {
                return $use['name'];
            },
            array_filter($uses, function (array $use): bool {
                return 'function' === $use['type'];
            })
        );

        $nextString = $phpcsFile->findNext([T_NAMESPACE, T_STRING], $stackPtr + 1);
        while (false !== $nextString && T_NAMESPACE !== $tokens[$nextString]['code']) {
            $this->processString($phpcsFile, $nextString, $functionUses);
            $nextString = $phpcsFile->findNext([T_NAMESPACE, T_STRING], $nextString + 1);
        }
    }

    /**
     * @param File  $phpcsFile
     * @param int   $stackPtr
     * @param array $functionUses
     *
     * @return void
     */
    private function processString(File $phpcsFile, int $stackPtr, array $functionUses): void
    {
        $tokens = $phpcsFile->getTokens();

        $ignore = [
            T_DOUBLE_COLON             => true,
            T_OBJECT_OPERATOR          => true,
            T_NULLSAFE_OBJECT_OPERATOR => true,
            T_FUNCTION                 => true,
            T_CONST                    => true,
            T_PUBLIC                   => true,
            T_PRIVATE                  => true,
            T_PROTECTED                => true,
            T_AS                       => true,
            T_NEW                      => true,
            T_INSTEADOF                => true,
            T_NS_SEPARATOR             => true,
            T_IMPLEMENTS               => true,
        ];

        $prevToken = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);

        // If function call is directly preceded by a NS_SEPARATOR it points to the
        // global namespace, so we should still catch it.
        if (T_NS_SEPARATOR === $tokens[$prevToken]['code']) {
            $prevToken = $phpcsFile->findPrevious(T_WHITESPACE, ($prevToken - 1), null, true);
            if (T_STRING === $tokens[$prevToken]['code']) {
                // Not in the global namespace.
                return;
            }
        }

        if (isset($ignore[$tokens[$prevToken]['code']])) {
            // Not a call to a PHP function.
            return;
        }

        $nextToken = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
        if (isset($ignore[$tokens[$nextToken]['code']])) {
            // Not a call to a PHP function.
            return;
        }

        if (T_STRING === $tokens[$stackPtr]['code'] && T_OPEN_PARENTHESIS !== $tokens[$nextToken]['code']) {
            // Not a call to a PHP function.
            return;
        }

        $function = mb_strtolower($tokens[$stackPtr]['content']);
        if (!in_array($function, $functionUses)) {
            $phpcsFile->addError(
                'PHP internal function "%s" must be imported',
                $stackPtr,
                'IncorrectOrder',
                [$function]
            );
        }
    }
}
