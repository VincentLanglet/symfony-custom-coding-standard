<?php

namespace SymfonyCustom\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Standards\PEAR\Sniffs\Commenting\FunctionCommentSniff as PEARFunctionCommentSniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * SymfonyCustom standard customization to PEARs FunctionCommentSniff.
 *
 * Verifies that :
 * <ul>
 *   <li>
 *     There is a &#64;return tag if a return statement exists inside the method
 *   </li>
 * </ul>
 */
class FunctionCommentSniff extends PEARFunctionCommentSniff
{
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
        $find   = Tokens::$methodPrefixes;
        $find[] = T_WHITESPACE;

        $commentEnd = $phpcsFile->findPrevious($find, ($stackPtr - 1), null, true);
        if (T_COMMENT === $tokens[$commentEnd]['code']) {
            // Inline comments might just be closing comments for control structures or functions
            // instead of function comments using the wrong comment type.
            // If there is other code on the line, assume they relate to that code.
            $prev = $phpcsFile->findPrevious($find, ($commentEnd - 1), null, true);
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

            if (($tokens[$stackPtr]['line'] - 1) !== $tokens[$commentEnd]['line']) {
                $error = 'There must be no blank lines after the function comment';
                $phpcsFile->addError($error, $commentEnd, 'SpacingAfter');
            }

            $commentStart = $tokens[$commentEnd]['comment_opener'];
            foreach ($tokens[$commentStart]['comment_tags'] as $tag) {
                if ('@see' === $tokens[$tag]['content']) {
                    // Make sure the tag isn't empty.
                    $string = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $tag, $commentEnd);
                    if (false === $string || $tokens[$string]['line'] !== $tokens[$tag]['line']) {
                        $error = 'Content missing for @see tag in function comment';
                        $phpcsFile->addError($error, $tag, 'EmptySees');
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
                    $error = 'Doc comment for parameter "%s" missing';
                    $data  = array($neededParam['name']);
                    $phpcsFile->addError($error, $stackPtr, 'MissingParamTag', $data);
                }
            }
        }

        $this->processWhitespace($phpcsFile, $commentStart);
    }

    /**
     * Process the return comment of this function comment.
     *
     * @param File     $phpcsFile    The file being scanned.
     * @param int      $stackPtr     The position of the current token in the stack passed in $tokens.
     * @param int|null $commentStart The position in the stack where the comment started.
     * @param bool     $hasComment   Use to specify if the function has comments to check
     *
     * @return void
     */
    protected function processReturn(
        File $phpcsFile,
        $stackPtr,
        $commentStart,
        $hasComment = true
    ) {
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
                        $error = 'Missing @return tag in function comment';
                        $phpcsFile->addError($error, $stackPtr, 'MissingReturn');
                    }

                    break;
                }
            }
        }
    }

    /**
     * @param File $phpcsFile
     * @param int  $commentStart
     * @param bool $hasComment
     */
    protected function processWhitespace(
        File $phpcsFile,
        $commentStart,
        $hasComment = true
    ) {
        $tokens = $phpcsFile->getTokens();
        $before = $phpcsFile->findPrevious(T_WHITESPACE, ($commentStart - 1), null, true);

        $startLine = $tokens[$commentStart]['line'];
        $prevLine = $tokens[$before]['line'];

        $found = $startLine - $prevLine - 1;

        // Skip for class opening
        if ($found < 1 && !(0 === $found && 'T_OPEN_CURLY_BRACKET' === $tokens[$before]['type'])) {
            if ($found < 0) {
                $found = 0;
            }

            if ($hasComment) {
                $error = 'Expected 1 blank line before docblock; %s found';
                $rule = 'SpacingBeforeDocblock';
            } else {
                $error = 'Expected 1 blank line before function; %s found';
                $rule = 'SpacingBeforeFunction';
            }

            $data = array($found);
            $fix = $phpcsFile->addFixableError($error, $commentStart, $rule, $data);

            if (true === $fix) {
                if ($found > 1) {
                    $phpcsFile->fixer->beginChangeset();

                    for ($i = ($before + 1); $i < ($commentStart - 1); $i++) {
                        $phpcsFile->fixer->replaceToken($i, '');
                    }

                    $phpcsFile->fixer->endChangeset();
                } else {
                    // Try and maintain indentation.
                    if (T_WHITESPACE === $tokens[($commentStart - 1)]['code']) {
                        $phpcsFile->fixer->addNewlineBefore($commentStart - 1);
                    } else {
                        $phpcsFile->fixer->addNewlineBefore($commentStart);
                    }
                }
            }
        }
    }

    /**
     * Is the comment an inheritdoc?
     *
     * @param File $phpcsFile The file being scanned.
     * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
     *
     * @return bool True if the comment is an inheritdoc
     */
    protected function isInheritDoc(File $phpcsFile, $stackPtr)
    {
        $start = $phpcsFile->findPrevious(T_DOC_COMMENT_OPEN_TAG, $stackPtr - 1);
        $end = $phpcsFile->findNext(T_DOC_COMMENT_CLOSE_TAG, $start);

        $content = $phpcsFile->getTokensAsString($start, ($end - $start));

        return preg_match('#{@inheritdoc}#i', $content) === 1;
    }

    /**
     * Process the function parameter comments.
     *
     * @param File $phpcsFile    The file being scanned.
     * @param int  $stackPtr     The position of the current token in the stack passed in $tokens.
     * @param int  $commentStart The position in the stack where the comment started.
     *
     * @return void
     */
    protected function processParams(
        File $phpcsFile,
        $stackPtr,
        $commentStart
    ) {
        if ($this->isInheritDoc($phpcsFile, $stackPtr)) {
            return;
        }

        parent::processParams($phpcsFile, $stackPtr, $commentStart);
    }

    /**
     * Is the return statement matching?
     *
     * @param array $tokens    Array of tokens
     * @param int   $returnPos Stack position of the T_RETURN token to process
     *
     * @return bool True if the return does not return anything
     */
    protected function isMatchingReturn($tokens, $returnPos)
    {
        do {
            $returnPos++;
        } while (T_WHITESPACE === $tokens[$returnPos]['code']);

        return T_SEMICOLON !== $tokens[$returnPos]['code'];
    }
}
