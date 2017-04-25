<?php

if (class_exists('PEAR_Sniffs_Commenting_FunctionCommentSniff', true) === false) {
    $error = 'Class PEAR_Sniffs_Commenting_FunctionCommentSniff not found';
    throw new PHP_CodeSniffer_Exception($error);
}

/**
 * Symfony3Custom standard customization to PEARs FunctionCommentSniff.
 *
 * Verifies that :
 * <ul>
 *   <li>
 *     There is a &#64;return tag if a return statement exists inside the method
 *   </li>
 * </ul>
 */
class Symfony3Custom_Sniffs_Commenting_FunctionCommentSniff extends PEAR_Sniffs_Commenting_FunctionCommentSniff
{
    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $find   = PHP_CodeSniffer_Tokens::$methodPrefixes;
        $find[] = T_WHITESPACE;

        $commentEnd = $phpcsFile->findPrevious($find, ($stackPtr - 1), null, true);
        if ($tokens[$commentEnd]['code'] === T_COMMENT) {
            // Inline comments might just be closing comments for
            // control structures or functions instead of function comments
            // using the wrong comment type. If there is other code on the line,
            // assume they relate to that code.
            $prev = $phpcsFile->findPrevious($find, ($commentEnd - 1), null, true);
            if ($prev !== false && $tokens[$prev]['line'] === $tokens[$commentEnd]['line']) {
                $commentEnd = $prev;
            }
        }

        $name = $phpcsFile->getDeclarationName($stackPtr);
        $commentRequired = strpos($name, 'test') !== 0
            && $name !== 'setUp'
            && $name !== 'tearDown';

        if ($tokens[$commentEnd]['code'] !== T_DOC_COMMENT_CLOSE_TAG
            && $tokens[$commentEnd]['code'] !== T_COMMENT
        ) {
            $hasComment = false;
            $phpcsFile->recordMetric($stackPtr, 'Function has doc comment', 'no');

            if ($commentRequired) {
                $phpcsFile->addError('Missing function doc comment', $stackPtr, 'Missing');

                return;
            } else {
                // The comment may not be required, we'll see in next checks
            }
        } else {
            $hasComment = true;
            $phpcsFile->recordMetric($stackPtr, 'Function has doc comment', 'yes');
        }

        if ($hasComment) {
            if ($tokens[$commentEnd]['code'] === T_COMMENT) {
                $phpcsFile->addError(
                    'You must use "/**" style comments for a function comment',
                    $stackPtr,
                    'WrongStyle'
                );

                return;
            }

            if ($tokens[$commentEnd]['line'] !== ($tokens[$stackPtr]['line'] - 1)) {
                $error = 'There must be no blank lines after the function comment';
                $phpcsFile->addError($error, $commentEnd, 'SpacingAfter');
            }

            $commentStart = $tokens[$commentEnd]['comment_opener'];
            foreach ($tokens[$commentStart]['comment_tags'] as $tag) {
                if ($tokens[$tag]['content'] === '@see') {
                    // Make sure the tag isn't empty.
                    $string = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $tag, $commentEnd);
                    if ($string === false || $tokens[$string]['line'] !== $tokens[$tag]['line']) {
                        $error = 'Content missing for @see tag in function comment';
                        $phpcsFile->addError($error, $tag, 'EmptySees');
                    }
                }
            }
        } else {
            // No comment but maybe a method prefix
            $methodPrefixes = $phpcsFile->findFirstOnLine(
                PHP_CodeSniffer_Tokens::$methodPrefixes,
                $stackPtr
            );

            if ($methodPrefixes !== false) {
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
            $this->processWhitespace($phpcsFile, $commentStart, $hasComment);

            if (count($realParams) > 0) {
                foreach ($realParams as $neededParam) {
                    $error = 'Doc comment for parameter "%s" missing';
                    $data  = array($neededParam['name']);
                    $phpcsFile->addError($error, $stackPtr, 'MissingParamTag', $data);
                }
            }
        }
    }

    /**
     * Process the return comment of this function comment.
     *
     * @param PHP_CodeSniffer_File $phpcsFile      The file being scanned.
     * @param int                  $stackPtr       The position of the current token
     *                                             in the stack passed in $tokens.
     * @param int|null             $commentStart   The position in the stack
     *                                             where the comment started.
     * @param bool                 $hasRealComment
     *
     * @return void
     */
    protected function processReturn(
        PHP_CodeSniffer_File $phpcsFile,
        $stackPtr,
        $commentStart,
        $hasRealComment = true
    ) {
        // Check for inheritDoc if there is comment
        if ($hasRealComment && $this->isInheritDoc($phpcsFile, $stackPtr)) {
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
                if ($tokens[$i]['code'] === T_CLOSURE) {
                    $i = $tokens[$i]['scope_closer'];
                    continue;
                }

                // Found a return not in a closure statement
                // Run the check on the first which is not only 'return;'
                if ($tokens[$i]['code'] === T_RETURN
                    && $this->isMatchingReturn($tokens, $i)
                ) {
                    if ($hasRealComment) {
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
     * @param PHP_CodeSniffer_File $phpcsFile
     * @param int                  $commentStart
     * @param bool                 $hasRealComment
     */
    protected function processWhitespace(
        PHP_CodeSniffer_File $phpcsFile,
        $commentStart,
        $hasRealComment = true
    ) {
        $tokens = $phpcsFile->getTokens();
        $before = $phpcsFile->findPrevious(T_WHITESPACE, ($commentStart - 1), null, true);

        $startLine = $tokens[$commentStart]['line'];
        $prevLine = $tokens[$before]['line'];

        $found = $startLine - $prevLine - 1;

        // Skip for class opening
        if ($found < 1 && !($found === 0 && $tokens[$before]['type'] === 'T_OPEN_CURLY_BRACKET')) {
            if ($found < 0) {
                $found = 0;
            }

            if ($hasRealComment) {
                $error = 'Expected 1 blank line before docblock; %s found';
                $rule = 'SpacingBeforeDocblock';
            } else {
                $error = 'Expected 1 blank line before function; %s found';
                $rule = 'SpacingBeforeFunction';
            }

            $data = array($found);
            $fix = $phpcsFile->addFixableError($error, $commentStart, $rule, $data);

            if ($fix === true) {
                if ($found > 1) {
                    $phpcsFile->fixer->beginChangeset();

                    for ($i = ($before + 1); $i < ($commentStart - 1); $i++) {
                        $phpcsFile->fixer->replaceToken($i, '');
                    }

                    $phpcsFile->fixer->endChangeset();
                } else {
                    // Try and maintain indentation.
                    if ($tokens[($commentStart - 1)]['code'] === T_WHITESPACE) {
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
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return bool True if the comment is an inheritdoc
     */
    protected function isInheritDoc(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $start = $phpcsFile->findPrevious(T_DOC_COMMENT_OPEN_TAG, $stackPtr - 1);
        $end = $phpcsFile->findNext(T_DOC_COMMENT_CLOSE_TAG, $start);

        $content = $phpcsFile->getTokensAsString($start, ($end - $start));

        return preg_match('#{@inheritdoc}#i', $content) === 1;
    }

    /**
     * Process the function parameter comments.
     *
     * @param PHP_CodeSniffer_File $phpcsFile    The file being scanned.
     * @param int                  $stackPtr     The position of the current token
     *                                           in the stack passed in $tokens.
     * @param int                  $commentStart The position in the stack
     *                                           where the comment started.
     *
     * @return void
     */
    protected function processParams(
        PHP_CodeSniffer_File $phpcsFile,
        $stackPtr,
        $commentStart
    ) {
        if ($this->isInheritDoc($phpcsFile, $stackPtr)) {
            return;
        }

        $this->processWhitespace($phpcsFile, $commentStart);

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
        } while ($tokens[$returnPos]['code'] === T_WHITESPACE);

        return $tokens[$returnPos]['code'] !== T_SEMICOLON;
    }
}
