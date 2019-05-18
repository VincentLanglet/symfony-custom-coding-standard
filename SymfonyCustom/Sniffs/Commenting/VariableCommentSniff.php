<?php

namespace SymfonyCustom\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\AbstractVariableSniff;

/**
 * Parses and verifies the variable doc comment.
 */
class VariableCommentSniff extends AbstractVariableSniff
{
    /**
     * Called to process class member vars.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
     *
     * @return void
     */
    public function processMemberVar(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $ignore = [
            T_PUBLIC,
            T_PRIVATE,
            T_PROTECTED,
            T_VAR,
            T_STATIC,
            T_WHITESPACE,
        ];

        $commentEnd = $phpcsFile->findPrevious($ignore, ($stackPtr - 1), null, true);
        if (false === $commentEnd
            || (T_DOC_COMMENT_CLOSE_TAG !== $tokens[$commentEnd]['code']
            && T_COMMENT !== $tokens[$commentEnd]['code'])
        ) {
            $phpcsFile->addError('Missing member variable doc comment', $stackPtr, 'Missing');

            return;
        }

        if (T_COMMENT === $tokens[$commentEnd]['code']) {
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
            if ('@var' === $tokens[$tag]['content']) {
                if (null !== $foundVar) {
                    $error = 'Only one @var tag is allowed in a member variable comment';
                    $phpcsFile->addError($error, $tag, 'DuplicateVar');
                } else {
                    $foundVar = $tag;
                }
            } elseif ('@see' === $tokens[$tag]['content']) {
                // Make sure the tag isn't empty.
                $string = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $tag, $commentEnd);
                if (false === $string || $tokens[$string]['line'] !== $tokens[$tag]['line']) {
                    $error = 'Content missing for @see tag in member variable comment';
                    $phpcsFile->addError($error, $tag, 'EmptySees');
                }
            }
        }

        // The @var tag is the only one we require.
        if (null === $foundVar) {
            $error = 'Missing @var tag in member variable comment';
            $phpcsFile->addError($error, $commentEnd, 'MissingVar');

            return;
        }

        $firstTag = $tokens[$commentStart]['comment_tags'][0];
        if (null !== $foundVar && '@var' !== $tokens[$firstTag]['content']) {
            $error = 'The @var tag must be the first tag in a member variable comment';
            $phpcsFile->addError($error, $foundVar, 'VarOrder');
        }

        // Make sure the tag isn't empty and has the correct padding.
        $string = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $foundVar, $commentEnd);
        if (false === $string || $tokens[$string]['line'] !== $tokens[$foundVar]['line']) {
            $error = 'Content missing for @var tag in member variable comment';
            $phpcsFile->addError($error, $foundVar, 'EmptyVar');

            return;
        }

        $content = explode(' ', $tokens[$string]['content']);
        $newContent = array_filter($content, function ($value) use ($tokens, $stackPtr) {
            return 0 === preg_match('/^\$/', $value);
        });
        if (count($newContent) < count($content)) {
            $error = '@var annotations should not contain variable name';
            $fix = $phpcsFile->addFixableError($error, $foundVar, 'NamedVar');

            if (true === $fix) {
                $phpcsFile->fixer->replaceToken($string, implode(' ', $newContent));
            }
        }

        $this->processWhitespace($phpcsFile, $commentStart);
    }

    /**
     * @param File $phpcsFile
     * @param int  $commentStart
     */
    protected function processWhitespace(
        File $phpcsFile,
        $commentStart
    ) {
        $tokens = $phpcsFile->getTokens();
        $before = $phpcsFile->findPrevious(T_WHITESPACE, ($commentStart - 1), null, true);

        $startLine = $tokens[$commentStart]['line'];
        $prevLine = $tokens[$before]['line'];

        $found = $startLine - $prevLine - 1;

        // Skip for class opening
        if ($found < 1
            && !(0 === $found
            && 'T_OPEN_CURLY_BRACKET' === $tokens[$before]['type'])
        ) {
            if ($found < 0) {
                $found = 0;
            }

            $error = 'Expected 1 blank line before docblock; %s found';
            $rule = 'SpacingBeforeDocblock';

            $data = [$found];
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
     * Called to process a normal variable.
     *
     * Not required for this sniff.
     *
     * @param File $phpcsFile The PHP_CodeSniffer file where this token was found.
     * @param int  $stackPtr  The position where the double quoted string was found.
     *
     * @return void
     */
    protected function processVariable(File $phpcsFile, $stackPtr)
    {
    }

    /**
     * Called to process variables found in double quoted strings.
     *
     * Not required for this sniff.
     *
     * @param File $phpcsFile The PHP_CodeSniffer file where this token was found.
     * @param int  $stackPtr  The position where the double quoted string was found.
     *
     * @return void
     */
    protected function processVariableInString(File $phpcsFile, $stackPtr)
    {
    }
}
