<?php

declare(strict_types=1);

namespace SymfonyCustom\Sniffs\Namespaces;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SymfonyCustom\Helpers\SniffHelper;

use function explode;
use function str_replace;
use function strcasecmp;

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

        $uses = SniffHelper::getUseStatements($phpcsFile, $stackPtr);

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

            $lastUse = $use;
        }

        return T_OPEN_TAG === $tokens[$stackPtr]['code'] ? $phpcsFile->numTokens + 1 : $stackPtr + 1;
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
