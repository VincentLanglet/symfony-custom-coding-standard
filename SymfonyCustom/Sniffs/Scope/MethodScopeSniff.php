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
     * Processes the function tokens within the class.
     *
     * @param File $phpcsFile The file where this token was found.
     * @param int  $stackPtr  The position where the token was found.
     * @param int  $currScope The current scope opener token.
     *
     * @return void
     */
    protected function processTokenWithinScope(
        File $phpcsFile,
        $stackPtr,
        $currScope
    ) {
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
     * Process tokens outside scope.
     *
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    protected function processTokenOutsideScope(File $phpcsFile, $stackPtr)
    {
    }
}
