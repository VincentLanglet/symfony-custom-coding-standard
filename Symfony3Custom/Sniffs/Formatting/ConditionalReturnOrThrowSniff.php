<?php

namespace Symfony3Custom\Sniffs\Formatting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Checks whether there are else(if) or break statements after return or throw
 */
class ConditionalReturnOrThrowSniff implements Sniff
{
    /**
     * @var array
     */
    private $openers = [
        T_IF,
        T_CASE,
    ];

    /**
     * @var array
     */
    private $conditions = [
        T_ELSEIF,
        T_ELSE,
        T_BREAK,
    ];

    /**
     * Registers the tokens that this sniff wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [
            T_THROW,
            T_RETURN,
        ];
    }

    /**
     * Called when one of the token types that this sniff is listening for is found.
     *
     * @param File $phpcsFile The PHP_CodeSniffer file where the token was found.
     * @param int  $stackPtr  The position in the PHP_CodeSniffer file's token stack
     *                        where the token was found.
     *
     * @return void|int Optionally returns a stack pointer. The sniff will not be
     *                  called again on the current file until the returned stack
     *                  pointer is reached. Return (count($tokens) + 1) to skip
     *                  the rest of the file.
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $opener = $phpcsFile->findPrevious($this->openers, $stackPtr);

        if ($opener
            && isset($tokens[$opener]['scope_closer'])
            && $stackPtr <= $tokens[$opener]['scope_closer']) {
            $isClosure = $phpcsFile->findPrevious(T_CLOSURE, $stackPtr, $opener);

            if (false !== $isClosure) {
                return;
            }

            $condition = $phpcsFile->findNext($this->conditions, $stackPtr + 1);

            if (false !== $condition) {
                $start = $stackPtr;
                $end = $condition;

                $next = $phpcsFile->findNext($this->openers, $start + 1, $end);
                while (false !== $next) {
                    if ($tokens[$condition]['level'] >= $tokens[$next]['level']) {
                        $err = false;
                        break;
                    }

                    $start = $next;
                    $next = $phpcsFile->findNext($this->openers, $start + 1, $end);
                }

                if (!isset($err)) {
                    $err = $tokens[$condition]['level'] === $tokens[$opener]['level'];
                }

                if (true === $err) {
                    $phpcsFile->addError(
                        'Do not use else, elseif, break after if and case conditions which return or throw something',
                        $condition,
                        'Invalid'
                    );
                }
            }
        }
    }
}
