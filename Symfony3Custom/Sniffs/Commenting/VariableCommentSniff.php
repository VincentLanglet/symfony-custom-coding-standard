<?php

if (class_exists('PHP_CodeSniffer_Standards_AbstractVariableSniff', true) === false) {
    throw new PHP_CodeSniffer_Exception('Class PHP_CodeSniffer_Standards_AbstractVariableSniff not found');
}

/**
 * Parses and verifies the variable doc comment.
 */
class Symfony3Custom_Sniffs_Commenting_VariableCommentSniff extends PHP_CodeSniffer_Standards_AbstractVariableSniff
{
    /**
     * Called to process class member vars.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function processMemberVar(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $ignore = array(
                   T_PUBLIC,
                   T_PRIVATE,
                   T_PROTECTED,
                   T_VAR,
                   T_STATIC,
                   T_WHITESPACE,
                  );

        $commentEnd = $phpcsFile->findPrevious($ignore, ($stackPtr - 1), null, true);
        if ($commentEnd === false
            || ($tokens[$commentEnd]['code'] !== T_DOC_COMMENT_CLOSE_TAG
            && $tokens[$commentEnd]['code'] !== T_COMMENT)
        ) {
            $phpcsFile->addError('Missing member variable doc comment', $stackPtr, 'Missing');

            return;
        }

        if ($tokens[$commentEnd]['code'] === T_COMMENT) {
            $phpcsFile->addError(
                'You must use "/**" style comments for a member variable comment',
                $stackPtr,
                'WrongStyle'
            );

            return;
        }

        $commentStart = $tokens[$commentEnd]['comment_opener'];

        $foundVar = null;
        foreach ($tokens[$commentStart]['comment_tags'] as $tag) {
            if ($tokens[$tag]['content'] === '@var') {
                if ($foundVar !== null) {
                    $error = 'Only one @var tag is allowed in a member variable comment';
                    $phpcsFile->addError($error, $tag, 'DuplicateVar');
                } else {
                    $foundVar = $tag;
                }
            } elseif ($tokens[$tag]['content'] === '@see') {
                // Make sure the tag isn't empty.
                $string = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $tag, $commentEnd);
                if ($string === false || $tokens[$string]['line'] !== $tokens[$tag]['line']) {
                    $error = 'Content missing for @see tag in member variable comment';
                    $phpcsFile->addError($error, $tag, 'EmptySees');
                }
            }
        }

        // The @var tag is the only one we require.
        if ($foundVar === null) {
            $error = 'Missing @var tag in member variable comment';
            $phpcsFile->addError($error, $commentEnd, 'MissingVar');

            return;
        }

        $firstTag = $tokens[$commentStart]['comment_tags'][0];
        if ($foundVar !== null && $tokens[$firstTag]['content'] !== '@var') {
            $error = 'The @var tag must be the first tag in a member variable comment';
            $phpcsFile->addError($error, $foundVar, 'VarOrder');
        }

        // Make sure the tag isn't empty and has the correct padding.
        $string = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $foundVar, $commentEnd);
        if ($string === false || $tokens[$string]['line'] !== $tokens[$foundVar]['line']) {
            $error = 'Content missing for @var tag in member variable comment';
            $phpcsFile->addError($error, $foundVar, 'EmptyVar');

            return;
        }

        $this->processWhitespace($phpcsFile, $commentStart);
    }

    /**
     * @param PHP_CodeSniffer_File $phpcsFile
     * @param int                  $commentStart
     */
    protected function processWhitespace(
        PHP_CodeSniffer_File $phpcsFile,
        $commentStart
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

            $error = 'Expected 1 blank line before docblock; %s found';
            $rule = 'SpacingBeforeDocblock';

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
     * Called to process a normal variable.
     *
     * Not required for this sniff.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The PHP_CodeSniffer file where this token was found.
     * @param int                  $stackPtr  The position where the double quoted
     *                                        string was found.
     *
     * @return void
     */
    protected function processVariable(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
    }

    /**
     * Called to process variables found in double quoted strings.
     *
     * Not required for this sniff.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The PHP_CodeSniffer file where this token was found.
     * @param int                  $stackPtr  The position where the double quoted
     *                                        string was found.
     *
     * @return void
     */
    protected function processVariableInString(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
    }
}
