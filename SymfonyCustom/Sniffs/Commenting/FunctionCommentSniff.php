<?php

declare(strict_types=1);

namespace SymfonyCustom\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Standards\PEAR\Sniffs\Commenting\FunctionCommentSniff as PEARFunctionCommentSniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * SymfonyCustom standard customization to PEARs FunctionCommentSniff.
 */
class FunctionCommentSniff extends PEARFunctionCommentSniff
{
    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $find = Tokens::$methodPrefixes;
        $find[] = T_WHITESPACE;

        $commentEnd = $phpcsFile->findPrevious($find, $stackPtr - 1, null, true);
        if (T_COMMENT === $tokens[$commentEnd]['code']) {
            // Inline comments might just be closing comments for control structures or functions
            // instead of function comments using the wrong comment type.
            // If there is other code on the line, assume they relate to that code.
            $prev = $phpcsFile->findPrevious($find, $commentEnd - 1, null, true);
            if (false !== $prev && $tokens[$prev]['line'] === $tokens[$commentEnd]['line']) {
                $commentEnd = $prev;
            }
        }

        $hasComment = T_DOC_COMMENT_CLOSE_TAG === $tokens[$commentEnd]['code']
            || T_COMMENT === $tokens[$commentEnd]['code'];

        if ($hasComment) {
            if (T_COMMENT === $tokens[$commentEnd]['code']) {
                $phpcsFile->addError(
                    'You must use "/**" style comments for a function comment',
                    $stackPtr,
                    'WrongStyle'
                );

                return;
            }

            if ($tokens[$stackPtr]['line'] - 1 !== $tokens[$commentEnd]['line']) {
                $phpcsFile->addError(
                    'There must be no blank lines after the function comment',
                    $commentEnd,
                    'SpacingAfter'
                );
            }

            $commentStart = $tokens[$commentEnd]['comment_opener'];
            foreach ($tokens[$commentStart]['comment_tags'] as $tag) {
                if ('@see' === $tokens[$tag]['content']) {
                    // Make sure the tag isn't empty.
                    $string = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $tag, $commentEnd);
                    if (false === $string || $tokens[$string]['line'] !== $tokens[$tag]['line']) {
                        $phpcsFile->addError('Content missing for @see tag in function comment', $tag, 'EmptySees');
                    }
                }
            }
        } else {
            // No comment but maybe a method prefix
            $methodPrefixes = $phpcsFile->findFirstOnLine(
                Tokens::$methodPrefixes,
                $stackPtr
            );

            if (false !== $methodPrefixes) {
                $commentStart = $methodPrefixes;
            } else {
                // No comment and no method prefix
                $commentStart = $stackPtr;
            }
        }

        $this->processReturn($phpcsFile, $stackPtr, $commentStart, $hasComment);

        $realParams = $phpcsFile->getMethodParameters($stackPtr);
        if ($hasComment) {
            // These checks need function comment
            $this->processParams($phpcsFile, $stackPtr, $commentStart);
            $this->processThrows($phpcsFile, $stackPtr, $commentStart);
        } else {
            if (count($realParams) > 0) {
                foreach ($realParams as $neededParam) {
                    $phpcsFile->addError(
                        'Doc comment for parameter "%s" missing',
                        $stackPtr,
                        'MissingParamTag',
                        [$neededParam['name']]
                    );
                }
            }
        }
    }

    /**
     * @param File     $phpcsFile
     * @param int      $stackPtr
     * @param int|null $commentStart
     * @param bool     $hasComment
     */
    protected function processReturn(File $phpcsFile, $stackPtr, $commentStart, $hasComment = true): void
    {
        // Check for inheritDoc if there is comment
        if ($hasComment && $this->isInheritDoc($phpcsFile, $stackPtr)) {
            return;
        }

        $tokens = $phpcsFile->getTokens();

        // Only check for a return comment if a non-void return statement exists
        if (isset($tokens[$stackPtr]['scope_opener'])) {
            // Start inside the function
            $start = $phpcsFile->findNext(
                T_OPEN_CURLY_BRACKET,
                $stackPtr,
                $tokens[$stackPtr]['scope_closer']
            );

            for ($i = $start; $i < $tokens[$stackPtr]['scope_closer']; ++$i) {
                // Skip closures
                if (T_CLOSURE === $tokens[$i]['code']) {
                    $i = $tokens[$i]['scope_closer'];
                    continue;
                }

                // Found a return not in a closure statement
                // Run the check on the first which is not only 'return;'
                if (T_RETURN === $tokens[$i]['code']
                    && $this->isMatchingReturn($tokens, $i)
                ) {
                    if ($hasComment) {
                        parent::processReturn($phpcsFile, $stackPtr, $commentStart);
                    } else {
                        // There is no doc and we need one with @return
                        $phpcsFile->addError(
                            'Missing @return tag in function comment',
                            $stackPtr,
                            'MissingReturn'
                        );
                    }

                    break;
                }
            }
        }
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     * @param int  $commentStart
     */
    protected function processThrows(File $phpcsFile, $stackPtr, $commentStart): void
    {
        $tokens = $phpcsFile->getTokens();

        $throw = null;
        foreach ($tokens[$commentStart]['comment_tags'] as $tag) {
            if ('@throws' === $tokens[$tag]['content']) {
                if (null !== $throw) {
                    $phpcsFile->addError(
                        'Only 1 @throws tag is allowed in a function comment',
                        $tag,
                        'DuplicateThrow'
                    );

                    return;
                }

                $throw = $tag;
            }
        }

        if (null !== $throw) {
            $exception = null;
            if (T_DOC_COMMENT_STRING === $tokens[$throw + 2]['code']) {
                $matches = [];
                preg_match('/([^\s]+)(?:\s+(.*))?/', $tokens[$throw + 2]['content'], $matches);
                $exception = $matches[1];
            }

            if (null === $exception) {
                $phpcsFile->addError(
                    'Exception type missing for @throws tag in function comment',
                    $throw,
                    'InvalidThrows'
                );
            }
        }
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     * @param int  $commentStart
     */
    protected function processParams(File $phpcsFile, $stackPtr, $commentStart): void
    {
        if ($this->isInheritDoc($phpcsFile, $stackPtr)) {
            return;
        }

        parent::processParams($phpcsFile, $stackPtr, $commentStart);
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     *
     * @return bool
     */
    private function isInheritDoc(File $phpcsFile, int $stackPtr): bool
    {
        $start = $phpcsFile->findPrevious(T_DOC_COMMENT_OPEN_TAG, $stackPtr - 1);
        $end = $phpcsFile->findNext(T_DOC_COMMENT_CLOSE_TAG, $start);

        $content = $phpcsFile->getTokensAsString($start, ($end - $start));

        return preg_match('#{@inheritdoc}#i', $content) === 1;
    }

    /**
     * @param array $tokens
     * @param int   $returnPos
     *
     * @return bool
     */
    private function isMatchingReturn(array $tokens, int $returnPos): bool
    {
        do {
            $returnPos++;
        } while (T_WHITESPACE === $tokens[$returnPos]['code']);

        return T_SEMICOLON !== $tokens[$returnPos]['code'];
    }
}
