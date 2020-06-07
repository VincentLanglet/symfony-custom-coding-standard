<?php

declare(strict_types=1);

namespace SymfonyCustom\Sniffs\Namespaces;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;
use SymfonyCustom\Helpers\SniffHelper;

/**
 * Checks for "use" statements that are not needed in a file.
 */
class UnusedUseSniff implements Sniff
{
    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_USE];
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        // Only check use statements in the global scope.
        if (!SniffHelper::isGlobalUse($phpcsFile, $stackPtr)) {
            return;
        }

        $tokens = $phpcsFile->getTokens();

        $semiColon = $phpcsFile->findEndOfStatement($stackPtr);
        $prev = $phpcsFile->findPrevious(Tokens::$emptyTokens, $semiColon - 1, null, true);

        if (T_CLOSE_USE_GROUP === $tokens[$prev]['code']) {
            $to = $prev;
            $from = $phpcsFile->findPrevious(T_OPEN_USE_GROUP, $prev - 1);

            // Empty group is invalid syntax
            if ($phpcsFile->findNext(Tokens::$emptyTokens, $from + 1, null, true) === $to) {
                $fix = $phpcsFile->addFixableError('Empty use group', $stackPtr, 'EmptyUseGroup');
                if ($fix) {
                    $this->removeUse($phpcsFile, $stackPtr, $semiColon);
                }

                return;
            }

            $comma = $phpcsFile->findNext(T_COMMA, $from + 1, $to);
            if (
                false === $comma
                || !$phpcsFile->findNext(Tokens::$emptyTokens, $comma + 1, $to, true)
            ) {
                $fix = $phpcsFile->addFixableError(
                    'Redundant use group for one declaration',
                    $stackPtr,
                    'RedundantUseGroup'
                );

                if ($fix) {
                    $phpcsFile->fixer->beginChangeset();
                    $phpcsFile->fixer->replaceToken($from, '');
                    $i = $from + 1;

                    while (T_WHITESPACE === $tokens[$i]['code']) {
                        $phpcsFile->fixer->replaceToken($i, '');
                        ++$i;
                    }

                    if (false !== $comma) {
                        $phpcsFile->fixer->replaceToken($comma, '');
                    }

                    $phpcsFile->fixer->replaceToken($to, '');
                    $i = $to - 1;
                    while (T_WHITESPACE === $tokens[$i]['code']) {
                        $phpcsFile->fixer->replaceToken($i, '');
                        --$i;
                    }
                    $phpcsFile->fixer->endChangeset();
                }

                return;
            }

            $skip = Tokens::$emptyTokens + [T_COMMA => T_COMMA];

            $classPtr = $phpcsFile->findPrevious($skip, $to - 1, $from + 1, true);
            while ($classPtr) {
                $to = $phpcsFile->findPrevious(T_COMMA, $classPtr - 1, $from + 1);

                if (!$this->isClassUsed($phpcsFile, $stackPtr, $classPtr)) {
                    $fix = $phpcsFile->addFixableError(
                        'Unused use statement "%s"',
                        $classPtr,
                        'UnusedUseInGroup',
                        [$tokens[$classPtr]['content']]
                    );

                    if ($fix) {
                        $first = false === $to ? $from + 1 : $to;
                        $last = $classPtr;
                        if (false === $to) {
                            $next = $phpcsFile->findNext(Tokens::$emptyTokens, $classPtr + 1, null, true);
                            if (T_COMMA === $tokens[$next]['code']) {
                                $last = $next;
                            }
                        }

                        $phpcsFile->fixer->beginChangeset();
                        for ($i = $first; $i <= $last; ++$i) {
                            $phpcsFile->fixer->replaceToken($i, '');
                        }
                        $phpcsFile->fixer->endChangeset();
                    }
                }

                if (false === $to) {
                    break;
                }

                $classPtr = $phpcsFile->findPrevious($skip, $to - 1, $from + 1, true);
            }

            return;
        }

        do {
            $classPtr = $phpcsFile->findPrevious(Tokens::$emptyTokens, $semiColon - 1, null, true);
            if (!$this->isClassUsed($phpcsFile, $stackPtr, $classPtr)) {
                $warning = 'Unused use statement "%s"';
                $data = [$tokens[$classPtr]['content']];
                $fix = $phpcsFile->addFixableError($warning, $stackPtr, 'UnusedUse', $data);

                if ($fix) {
                    $prev = $phpcsFile->findPrevious(
                        Tokens::$emptyTokens + [
                            T_STRING       => T_STRING,
                            T_NS_SEPARATOR => T_NS_SEPARATOR,
                            T_AS           => T_AS,
                        ],
                        $classPtr,
                        null,
                        true
                    );

                    $to = $semiColon;
                    if (T_COMMA === $tokens[$prev]['code']) {
                        $from = $prev;
                        $to = $classPtr;
                    } elseif (T_SEMICOLON === $tokens[$semiColon]['code']) {
                        $from = $stackPtr;
                    } else {
                        $from = $phpcsFile->findNext(Tokens::$emptyTokens, $prev + 1, null, true);
                        if (
                            T_STRING === $tokens[$from]['code']
                            && in_array(strtolower($tokens[$from]['content']), ['const', 'function'], true)
                        ) {
                            $from = $phpcsFile->findNext(Tokens::$emptyTokens, $from + 1, null, true);
                        }
                    }

                    $this->removeUse($phpcsFile, $from, $to);
                }
            }

            if (T_SEMICOLON === $tokens[$semiColon]['code']) {
                break;
            }

            $semiColon = $phpcsFile->findEndOfStatement($semiColon + 1);
        } while ($semiColon);
    }

    /**
     * @param File $phpcsFile
     * @param int  $from
     * @param int  $to
     *
     * @return void
     */
    private function removeUse(File $phpcsFile, int $from, int $to): void
    {
        $tokens = $phpcsFile->getTokens();

        $phpcsFile->fixer->beginChangeset();

        // Remote whitespaces before in the same line
        if (
            T_WHITESPACE === $tokens[$from - 1]['code']
            && $tokens[$from - 1]['line'] === $tokens[$from]['line']
            && $tokens[$from - 2]['line'] !== $tokens[$from]['line']
        ) {
            $phpcsFile->fixer->replaceToken($from - 1, '');
        }

        for ($i = $from; $i <= $to; ++$i) {
            $phpcsFile->fixer->replaceToken($i, '');
        }

        // Also remove whitespace after the semicolon (new lines).
        if (isset($tokens[$to + 1]) && T_WHITESPACE === $tokens[$to + 1]['code']) {
            $phpcsFile->fixer->replaceToken($to + 1, '');
        }
        $phpcsFile->fixer->endChangeset();
    }

    /**
     * @param File $phpcsFile
     * @param int  $usePtr
     * @param int  $classPtr
     *
     * @return bool
     */
    private function isClassUsed(File $phpcsFile, int $usePtr, int $classPtr): bool
    {
        $tokens = $phpcsFile->getTokens();

        // Search where the class name is used. PHP treats class names case
        // insensitive, that's why we cannot search for the exact class name string
        // and need to iterate over all T_STRING tokens in the file.
        $classUsed = $phpcsFile->findNext(
            [T_STRING, T_DOC_COMMENT_STRING, T_DOC_COMMENT_TAG, T_NAMESPACE],
            $classPtr + 1
        );
        $className = $tokens[$classPtr]['content'];

        // Check if the referenced class is in the same namespace as the current
        // file. If it is then the use statement is not necessary.
        $namespacePtr = $phpcsFile->findPrevious(T_NAMESPACE, $usePtr);
        while (false !== $namespacePtr && false === SniffHelper::isNamespace($phpcsFile, $namespacePtr)) {
            $phpcsFile->findPrevious(T_NAMESPACE, $namespacePtr - 1);
        }

        $namespaceEnd = false !== $namespacePtr && isset($tokens[$namespacePtr]['scope_closer'])
            ? $tokens[$namespacePtr]['scope_closer']
            : null;

        $type = 'class';
        $next = $phpcsFile->findNext(Tokens::$emptyTokens, $usePtr + 1, null, true);
        if (
            T_STRING === $tokens[$next]['code']
            && in_array(strtolower($tokens[$next]['content']), ['const', 'function'], true)
        ) {
            $type = strtolower($tokens[$next]['content']);
        }

        $searchName = 'const' === $type ? $className : strtolower($className);

        $prev = $phpcsFile->findPrevious(
            Tokens::$emptyTokens + [
                T_STRING       => T_STRING,
                T_NS_SEPARATOR => T_NS_SEPARATOR,
            ],
            $classPtr - 1,
            null,
            $usePtr
        );

        // Only if alias is not used.
        if (T_AS !== $tokens[$prev]['code']) {
            $isGroup = T_OPEN_USE_GROUP === $tokens[$prev]['code']
                || false !== $phpcsFile->findPrevious(T_OPEN_USE_GROUP, $prev, $usePtr);

            $useNamespace = '';
            if ($isGroup || T_COMMA !== $tokens[$prev]['code']) {
                $useNamespacePtr = 'class' === $type ? $next : $next + 1;
                $useNamespace = $this->getNamespace(
                    $phpcsFile,
                    $useNamespacePtr,
                    [T_OPEN_USE_GROUP, T_COMMA, T_AS, T_SEMICOLON]
                );

                if ($isGroup) {
                    $useNamespace .= '\\';
                }
            }

            if (T_COMMA === $tokens[$prev]['code'] || T_OPEN_USE_GROUP === $tokens[$prev]['code']) {
                $useNamespace .= $this->getNamespace(
                    $phpcsFile,
                    $prev + 1,
                    [T_CLOSE_USE_GROUP, T_COMMA, T_AS, T_SEMICOLON]
                );
            }

            $useNamespace = substr($useNamespace, 0, strrpos($useNamespace, '\\') ?: 0);

            if (false !== $namespacePtr) {
                $namespace = $this->getNamespace($phpcsFile, $namespacePtr + 1, [T_CURLY_OPEN, T_SEMICOLON]);

                if (0 === strcasecmp($namespace, $useNamespace)) {
                    $classUsed = false;
                }
            } elseif (false === $namespacePtr && '' === $useNamespace) {
                $classUsed = false;
            }
        }

        $emptyTokens = Tokens::$emptyTokens;
        unset($emptyTokens[T_DOC_COMMENT_TAG]);

        while (false !== $classUsed && false === SniffHelper::isNamespace($phpcsFile, $classUsed)) {
            $isStringToken = T_STRING === $tokens[$classUsed]['code'];

            $match = null;

            if (
                ($isStringToken
                    && (('const' !== $type && strtolower($tokens[$classUsed]['content']) === $searchName)
                        || ('const' === $type && $tokens[$classUsed]['content'] === $searchName)))
                || ('class' === $type
                    && ((T_DOC_COMMENT_STRING === $tokens[$classUsed]['code']
                            && preg_match(
                                '/(\s|\||\&|\(|\<|\,|^)'.preg_quote($searchName, '/').'(\s|\||\&|\\\\|\<|\,|\>|\}|$|\[\])/i',
                                $tokens[$classUsed]['content']
                            ))
                        || (T_DOC_COMMENT_TAG === $tokens[$classUsed]['code']
                            && preg_match(
                                '/@'.preg_quote($searchName, '/').'(\(|\\\\|$)/i',
                                $tokens[$classUsed]['content']
                            ))
                        || (!$isStringToken
                            && !preg_match(
                                '/"[^"]*'.preg_quote($searchName, '/').'\b[^"]*"/i',
                                $tokens[$classUsed]['content']
                            )
                            && preg_match(
                                '/(?<!")@'.preg_quote($searchName, '/').'\b/i',
                                $tokens[$classUsed]['content'],
                                $match
                            ))))
            ) {
                $beforeUsage = $phpcsFile->findPrevious(
                    $isStringToken ? Tokens::$emptyTokens : $emptyTokens,
                    $classUsed - 1,
                    null,
                    true
                );

                if ($isStringToken) {
                    if ($this->determineType($phpcsFile, $beforeUsage, $classUsed) === $type) {
                        return true;
                    }
                } elseif (T_DOC_COMMENT_STRING === $tokens[$classUsed]['code']) {
                    if (
                        T_DOC_COMMENT_TAG === $tokens[$beforeUsage]['code']
                        && in_array($tokens[$beforeUsage]['content'], SniffHelper::TAGS_WITH_TYPE, true)
                    ) {
                        return true;
                    }

                    if ($match) {
                        return true;
                    }
                } else {
                    return true;
                }
            }

            $classUsed = $phpcsFile->findNext(
                [T_STRING, T_DOC_COMMENT_STRING, T_DOC_COMMENT_TAG, T_NAMESPACE],
                $classUsed + 1,
                $namespaceEnd
            );
        }

        return false;
    }

    /**
     * @param File  $phpcsFile
     * @param int   $ptr
     * @param array $stop
     *
     * @return string
     */
    private function getNamespace(File $phpcsFile, int $ptr, array $stop): string
    {
        $tokens = $phpcsFile->getTokens();

        $result = '';
        while (!in_array($tokens[$ptr]['code'], $stop, true)) {
            if (in_array($tokens[$ptr]['code'], [T_STRING, T_NS_SEPARATOR], true)) {
                $result .= $tokens[$ptr]['content'];
            }

            ++$ptr;
        }

        return trim(trim($result), '\\');
    }

    /**
     * @param File $phpcsFile
     * @param int  $beforePtr
     * @param int  $ptr
     *
     * @return string|null
     */
    private function determineType(File $phpcsFile, int $beforePtr, int $ptr): ?string
    {
        $tokens = $phpcsFile->getTokens();

        $beforeCode = $tokens[$beforePtr]['code'];

        if (
            in_array(
                $beforeCode,
                [T_NS_SEPARATOR, T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION, T_CONST, T_AS, T_INSTEADOF],
                true
            )
        ) {
            return null;
        }

        if (
            in_array(
                $beforeCode,
                [T_NEW, T_NULLABLE, T_EXTENDS, T_IMPLEMENTS, T_INSTANCEOF],
                true
            )
        ) {
            return 'class';
        }

        // Trait usage
        if (T_USE === $beforeCode) {
            if (SniffHelper::isTraitUse($phpcsFile, $beforePtr)) {
                return 'class';
            }

            return null;
        }

        if (T_COMMA === $beforeCode) {
            $prev = $phpcsFile->findPrevious(
                Tokens::$emptyTokens + [
                    T_STRING       => T_STRING,
                    T_NS_SEPARATOR => T_NS_SEPARATOR,
                    T_COMMA        => T_COMMA,
                ],
                $beforePtr - 1,
                null,
                true
            );

            if (T_IMPLEMENTS === $tokens[$prev]['code'] || T_EXTENDS === $tokens[$prev]['code']) {
                return 'class';
            }
        }

        $afterPtr = $phpcsFile->findNext(Tokens::$emptyTokens, $ptr + 1, null, true);
        $afterCode = $tokens[$afterPtr]['code'];

        if (T_AS === $afterCode) {
            return null;
        }

        if (T_OPEN_PARENTHESIS === $afterCode) {
            return 'function';
        }

        if (
            in_array(
                $afterCode,
                [T_DOUBLE_COLON, T_VARIABLE, T_ELLIPSIS, T_NS_SEPARATOR, T_OPEN_CURLY_BRACKET],
                true
            )
        ) {
            return 'class';
        }

        if (T_COLON === $beforeCode) {
            $prev = $phpcsFile->findPrevious(Tokens::$emptyTokens, $beforePtr - 1, null, true);
            if (
                false !== $prev
                && T_CLOSE_PARENTHESIS === $tokens[$prev]['code']
                && isset($tokens[$prev]['parenthesis_owner'])
                && T_FUNCTION === $tokens[$tokens[$prev]['parenthesis_owner']]['code']
            ) {
                return 'class';
            }
        }

        if (T_BITWISE_OR === $afterCode) {
            $next = $phpcsFile->findNext(
                Tokens::$emptyTokens + [
                    T_BITWISE_OR   => T_BITWISE_OR,
                    T_STRING       => T_STRING,
                    T_NS_SEPARATOR => T_NS_SEPARATOR,
                ],
                $afterPtr,
                null,
                true
            );

            if (T_VARIABLE === $tokens[$next]['code']) {
                return 'class';
            }
        }

        return 'const';
    }
}
