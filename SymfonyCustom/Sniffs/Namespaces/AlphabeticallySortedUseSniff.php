<?php

namespace SymfonyCustom\Sniffs\Namespaces;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;
use SymfonyCustom\Sniffs\SniffHelper;

/**
 * Class AlphabeticallySortedUseSniff
 */
class AlphabeticallySortedUseSniff implements Sniff
{
    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_OPEN_TAG, T_NAMESPACE];
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     *
     * @return int
     */
    public function process(File $phpcsFile, $stackPtr): int
    {
        $tokens = $phpcsFile->getTokens();

        if (!SniffHelper::isNamespace($phpcsFile, $stackPtr)) {
            $namespace = $phpcsFile->findNext(T_NAMESPACE, $stackPtr + 1);

            if ($namespace) {
                return $namespace;
            }
        }

        $uses = $this->getUseStatements($phpcsFile, $stackPtr);

        $lastUse = null;
        foreach ($uses as $use) {
            if (!$lastUse) {
                $lastUse = $use;
                continue;
            }

            $order = $this->compareUseStatements($use, $lastUse);

            if ($order < 0) {
                $phpcsFile->addError(
                    'Use statements are incorrectly ordered. The first wrong one is %s',
                    $use['ptrUse'],
                    'IncorrectOrder',
                    [$use['name']]
                );

                return $stackPtr + 1;
            }

            // Check empty lines between use statements.
            // There must be no empty lines between use statements.
            $lineDiff = $tokens[$use['ptrUse']]['line'] - $tokens[$lastUse['ptrUse']]['line'];
            if ($lineDiff > 1) {
                $fix = $phpcsFile->addFixableError(
                    'There must not be any empty line between use statement',
                    $use['ptrUse'],
                    'EmptyLine'
                );

                if ($fix) {
                    $phpcsFile->fixer->beginChangeset();
                    for ($i = $lastUse['ptrEnd'] + 1; $i < $use['ptrUse']; ++$i) {
                        if (false !== strpos($tokens[$i]['content'], $phpcsFile->eolChar)) {
                            $phpcsFile->fixer->replaceToken($i, '');
                            --$lineDiff;

                            if (1 === $lineDiff) {
                                break;
                            }
                        }
                    }
                    $phpcsFile->fixer->endChangeset();
                }
            } elseif (0 === $lineDiff) {
                $fix = $phpcsFile->addFixableError(
                    'Each use statement must be in new line',
                    $use['ptrUse'],
                    'TheSameLine'
                );

                if ($fix) {
                    $phpcsFile->fixer->addNewline($lastUse['ptrEnd']);
                }
            }

            $lastUse = $use;
        }

        return T_OPEN_TAG === $tokens[$stackPtr]['code']
            ? $phpcsFile->numTokens + 1
            : $stackPtr + 1;
    }

    /**
     * @param File $phpcsFile
     * @param int  $scopePtr
     *
     * @return array
     */
    private function getUseStatements(File $phpcsFile, int $scopePtr): array
    {
        $tokens = $phpcsFile->getTokens();

        $uses = [];

        if (isset($tokens[$scopePtr]['scope_opener'])) {
            $start = $tokens[$scopePtr]['scope_opener'];
            $end = $tokens[$scopePtr]['scope_closer'];
        } else {
            $start = $scopePtr;
            $end = null;
        }

        $use = $phpcsFile->findNext(T_USE, $start + 1, $end);
        while (false !== $use && T_USE === $tokens[$use]['code']) {
            if (!SniffHelper::isGlobalUse($phpcsFile, $use)
                || (null !== $end
                    && (!isset($tokens[$use]['conditions'][$scopePtr])
                        || $tokens[$use]['level'] !== $tokens[$scopePtr]['level'] + 1))
            ) {
                $use = $phpcsFile->findNext(Tokens::$emptyTokens, $use + 1, $end, true);
                continue;
            }

            // find semicolon as the end of the global use scope
            $endOfScope = $phpcsFile->findNext(T_SEMICOLON, $use + 1);

            $startOfName = $phpcsFile->findNext([T_STRING, T_NS_SEPARATOR], $use + 1, $endOfScope);

            $type = 'class';
            if (T_STRING === $tokens[$startOfName]['code']) {
                $lowerContent = strtolower($tokens[$startOfName]['content']);
                if ('function' === $lowerContent || 'const' === $lowerContent) {
                    $type = $lowerContent;

                    $startOfName = $phpcsFile->findNext([T_STRING, T_NS_SEPARATOR], $startOfName + 1, $endOfScope);
                }
            }

            $uses[] = [
                'ptrUse' => $use,
                'name'   => trim($phpcsFile->getTokensAsString($startOfName, $endOfScope - $startOfName)),
                'ptrEnd' => $endOfScope,
                'string' => trim($phpcsFile->getTokensAsString($use, $endOfScope - $use + 1)),
                'type'   => $type,
            ];

            $use = $phpcsFile->findNext(Tokens::$emptyTokens, $endOfScope + 1, $end, true);
        }

        return $uses;
    }

    /**
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    private function compareUseStatements(array $a, array $b): int
    {
        if ($a['type'] === $b['type']) {
            return strcasecmp(
                $this->clearName($a['name']),
                $this->clearName($b['name'])
            );
        }

        if ('class' === $a['type'] || ('function' === $a['type'] && 'const' === $b['type'])) {
            return -1;
        }

        return 1;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function clearName(string $name): string
    {
        // Handle grouped use
        $name = explode('{', $name)[0];

        return str_replace('\\', ' ', $name);
    }
}
