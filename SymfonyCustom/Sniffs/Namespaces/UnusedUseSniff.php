<?php

namespace SymfonyCustom\Sniffs\Namespaces;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Checks for "use" statements that are not needed in a file.
 */
class UnusedUseSniff implements Sniff
{
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
     * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Only check use statements in the global scope.
        if (empty($tokens[$stackPtr]['conditions']) === false) {
            return;
        }

        // Seek to the end of the statement and get the string before the semi colon.
        $semiColon = $phpcsFile->findEndOfStatement($stackPtr);
        if (T_SEMICOLON !== $tokens[$semiColon]['code']) {
            return;
        }

        $classPtr = $phpcsFile->findPrevious(
            Tokens::$emptyTokens,
            ($semiColon - 1),
            null,
            true
        );

        if (T_STRING !== $tokens[$classPtr]['code']) {
            return;
        }

        // Search where the class name is used. PHP treats class names case insensitive,
        // that's why we cannot search for the exact class name string
        // and need to iterate over all T_STRING tokens in the file.
        $classUsed      = $phpcsFile->findNext(T_STRING, ($classPtr + 1));
        $lowerClassName = strtolower($tokens[$classPtr]['content']);

        // Check if the referenced class is in the same namespace as the current file.
        // If it is then the use statement is not necessary.
        $namespacePtr = $phpcsFile->findPrevious([T_NAMESPACE], $stackPtr);

        // Check if the use statement does aliasing with the "as" keyword.
        // Aliasing is allowed even in the same namespace.
        $aliasUsed = $phpcsFile->findPrevious(T_AS, ($classPtr - 1), $stackPtr);

        if (false !== $namespacePtr && false === $aliasUsed) {
            $nsEnd = $phpcsFile->findNext(
                [T_NS_SEPARATOR, T_STRING, T_WHITESPACE],
                ($namespacePtr + 1),
                null,
                true
            );

            $namespace = trim($phpcsFile->getTokensAsString(($namespacePtr + 1), ($nsEnd - $namespacePtr - 1)));

            $useNamespacePtr = $phpcsFile->findNext([T_STRING], ($stackPtr + 1));
            $useNamespaceEnd = $phpcsFile->findNext(
                [T_NS_SEPARATOR, T_STRING],
                ($useNamespacePtr + 1),
                null,
                true
            );

            $useNamespace = rtrim(
                $phpcsFile->getTokensAsString(
                    $useNamespacePtr,
                    ($useNamespaceEnd - $useNamespacePtr - 1)
                ),
                '\\'
            );

            if (strcasecmp($namespace, $useNamespace) === 0) {
                $classUsed = false;
            }
        }

        while (false !== $classUsed) {
            if (strtolower($tokens[$classUsed]['content']) === $lowerClassName) {
                $beforeUsage = $phpcsFile->findPrevious(
                    Tokens::$emptyTokens,
                    ($classUsed - 1),
                    null,
                    true
                );

                // If a backslash is used before the class name then this is some other use statement.
                if (T_USE !== $tokens[$beforeUsage]['code'] && T_NS_SEPARATOR !== $tokens[$beforeUsage]['code']) {
                    return;
                }

                // Trait use statement within a class.
                if (T_USE === $tokens[$beforeUsage]['code'] && false === empty($tokens[$beforeUsage]['conditions'])) {
                    return;
                }
            }

            $classUsed = $phpcsFile->findNext([T_STRING], ($classUsed + 1));
        }

        // More checks
        foreach ($tokens as $token) {
            // Check for doc params @...
            if ('T_DOC_COMMENT_TAG' === $token['type']) {
                // Handle comment tag as @Route(..) or @ORM\Id
                if (preg_match('/^@'.$lowerClassName.'(?![a-zA-Z])/i', $token['content']) === 1) {
                    return;
                };
            }

            // Check for @param Truc or @return Machin
            if ('T_DOC_COMMENT_STRING' === $token['type']) {
                if (trim(strtolower($token['content'])) === $lowerClassName
                    // Handle @var Machin[]|Machine|AnotherMachin $machin
                    || preg_match('/^'.$lowerClassName.'(\|| |\[)/i', trim($token['content'])) === 1
                    || preg_match('/(\|| )'.$lowerClassName.'(\|| |\[)/i', trim($token['content'])) === 1
                    || preg_match('/(\|| )'.$lowerClassName.'$/i', trim($token['content'])) === 1) {
                    $beforeUsage = $phpcsFile->findPrevious(
                        Tokens::$emptyTokens,
                        ($classUsed - 1),
                        null,
                        true
                    );

                    // If a backslash is used before the class name then this is some other use statement.
                    if (T_USE !== $tokens[$beforeUsage]['code'] && T_NS_SEPARATOR !== $tokens[$beforeUsage]['code']) {
                        return;
                    }
                }
            }
        }

        $fix = $phpcsFile->addFixableError('Unused use statement', $stackPtr, 'UnusedUse');
        if (true === $fix) {
            // Remove the whole use statement line.
            $phpcsFile->fixer->beginChangeset();
            for ($i = $stackPtr; $i <= $semiColon; $i++) {
                $phpcsFile->fixer->replaceToken($i, '');
            }

            // Also remove whitespace after the semicolon (new lines).
            while (true === isset($tokens[$i]) && T_WHITESPACE === $tokens[$i]['code']) {
                $phpcsFile->fixer->replaceToken($i, '');
                if (strpos($tokens[$i]['content'], $phpcsFile->eolChar) !== false) {
                    break;
                }

                $i++;
            }

            $phpcsFile->fixer->endChangeset();
        }
    }
}
