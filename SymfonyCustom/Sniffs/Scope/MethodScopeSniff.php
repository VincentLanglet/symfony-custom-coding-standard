<?php

namespace SymfonyCustom\Sniffs\Scope;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\AbstractScopeSniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Verifies that class members have scope modifiers.
 */
class MethodScopeSniff extends AbstractScopeSniff
{
    /**
     * MethodScopeSniff constructor
     */
    public function __construct()
    {
        parent::__construct([T_CLASS], [T_FUNCTION]);
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     * @param int  $currScope
     */
    protected function processTokenWithinScope(File $phpcsFile, $stackPtr, $currScope): void
    {
        $tokens = $phpcsFile->getTokens();

        $methodName = $phpcsFile->getDeclarationName($stackPtr);
        if (null === $methodName) {
            // Ignore closures.
            return;
        }

        $modifier = $phpcsFile->findPrevious(
            Tokens::$scopeModifiers,
            $stackPtr
        );

        if ((false === $modifier)
            || ($tokens[$modifier]['line'] !== $tokens[$stackPtr]['line'])
        ) {
            $error = 'No scope modifier specified for function "%s"';
            $data  = [$methodName];
            $phpcsFile->addError($error, $stackPtr, 'Missing', $data);
        }
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    protected function processTokenOutsideScope(File $phpcsFile, $stackPtr): void
    {
    }
}
