<?php

namespace Symfony3Custom\Sniffs\Namespaces;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Ensures USE blocks are alphabetically sorted.
 */
class AlphabeticallySortedUseSniff implements Sniff
{
    /**
     * @var bool
     */
    private $caseSensitive = false;

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_USE);
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int  $stackPtr  The position of the current token in
     *                        the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        if (true === $this->shouldIgnoreUse($phpcsFile, $stackPtr)) {
            return;
        }

        $previousUse = $phpcsFile->findPrevious(T_USE, $stackPtr - 1);

        if (false === $previousUse) {
            return;
        }

        // Look for the real previous USE
        while (true === $this->shouldIgnoreUse($phpcsFile, $previousUse)) {
            $previousUse = $phpcsFile->findPrevious(T_USE, $previousUse - 1);

            if (false === $previousUse) {
                return;
            }
        }

        $namespace = $this->getNamespaceUsed($phpcsFile, $stackPtr);
        $previousNamespace = $this->getNamespaceUsed($phpcsFile, $previousUse);

        if ($this->compareNamespaces($namespace, $previousNamespace) < 0) {
            $error = 'Namespaces used are not correctly sorted';
            $phpcsFile->addError($error, $stackPtr, 'AlphabeticallySortedUse');
        }
    }

    /**
     * Check if this USE statement is part of the namespace block.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int  $stackPtr  The position of the current token in
     *                        the stack passed in $tokens.
     *
     * @return bool
     */
    private function shouldIgnoreUse(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Ignore USE keywords inside closures.
        $next = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
        if (T_OPEN_PARENTHESIS === $tokens[$next]['code']) {
            return true;
        }

        // Ignore USE keywords inside class and trait
        if ($phpcsFile->hasCondition($stackPtr, array(T_CLASS, T_TRAIT)) === true) {
            return true;
        }

        return false;
    }

    /**
     * Get full namespace imported
     *
     * @param File $phpcsFile The file being scanned.
     * @param int  $stackPtr  The position of the current token in
     *                        the stack passed in $tokens.
     *
     * @return string
     */
    private function getNamespaceUsed(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $namespace = '';

        $start = $phpcsFile->findNext([T_STRING, T_NS_SEPARATOR], $stackPtr);
        $end = $phpcsFile->findNext([T_STRING, T_NS_SEPARATOR], $start, null, true);

        for ($i = $start; $i < $end; $i++) {
            $namespace .= $tokens[$i]['content'];
        }

        return $namespace;
    }

    /**
     * @param string $namespace1
     * @param string $namespace2
     *
     * @return int
     */
    private function compareNamespaces($namespace1, $namespace2)
    {
        $array1  = explode('\\', $namespace1);
        $length1 = count($array1);
        $array2  = explode('\\', $namespace2);
        $length2 = count($array2);

        for ($i = 0; $i < $length1; $i++) {
            if ($i >= $length2) {
                // $namespace2 is shorter than $namespace1
                // and they have the same beginning
                // so $namespace1 > $namespace2
                return 1;
            }

            if (true === $this->caseSensitive && strcmp($array1[$i], $array2[$i]) !== 0) {
                return strcmp($array1[$i], $array2[$i]);
            } elseif (false === $this->caseSensitive && strcasecmp($array1[$i], $array2[$i]) !== 0) {
                return strcasecmp($array1[$i], $array2[$i]);
            }
        }

        if ($length1 === $length2) {
            return 0;
        }

        // $namespace1 is shorter than $namespace2
        // and they have the same beginning
        // so $namespace1 < $namespace2
        return -1;
    }
}
