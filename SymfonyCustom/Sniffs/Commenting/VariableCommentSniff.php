<?php

declare(strict_types=1);

namespace SymfonyCustom\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\AbstractVariableSniff;

/**
 * Parses and verifies the variable doc comment.
 */
class VariableCommentSniff extends AbstractVariableSniff
{
    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    public function processMemberVar(File $phpcsFile, $stackPtr): void
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

        $commentEnd = $phpcsFile->findPrevious($ignore, $stackPtr - 1, null, true);
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
                    $phpcsFile->addError(
                        'Only one @var tag is allowed in a member variable comment',
                        $tag,
                        'DuplicateVar'
                    );
                } else {
                    $foundVar = $tag;
                }
            } elseif ('@see' === $tokens[$tag]['content']) {
                // Make sure the tag isn't empty.
                $string = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $tag, $commentEnd);
                if (false === $string || $tokens[$string]['line'] !== $tokens[$tag]['line']) {
                    $phpcsFile->addError('Content missing for @see tag in member variable comment', $tag, 'EmptySees');
                }
            }
        }

        // The @var tag is the only one we require.
        if (null === $foundVar) {
            $phpcsFile->addError('Missing @var tag in member variable comment', $commentEnd, 'MissingVar');

            return;
        }

        $firstTag = $tokens[$commentStart]['comment_tags'][0];
        if (null !== $foundVar && '@var' !== $tokens[$firstTag]['content']) {
            $phpcsFile->addError(
                'The @var tag must be the first tag in a member variable comment',
                $foundVar,
                'VarOrder'
            );
        }

        // Make sure the tag isn't empty and has the correct padding.
        $string = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $foundVar, $commentEnd);
        if (false === $string || $tokens[$string]['line'] !== $tokens[$foundVar]['line']) {
            $phpcsFile->addError('Content missing for @var tag in member variable comment', $foundVar, 'EmptyVar');

            return;
        }

        $content = explode(' ', $tokens[$string]['content']);
        $newContent = array_filter($content, function ($value) use ($tokens, $stackPtr) {
            return 0 === preg_match('/^\$/', $value);
        });
        if (count($newContent) < count($content)) {
            $fix = $phpcsFile->addFixableError(
                '@var annotations should not contain variable name',
                $foundVar,
                'NamedVar'
            );

            if ($fix) {
                $phpcsFile->fixer->replaceToken($string, implode(' ', $newContent));
            }
        }

        $this->processWhitespace($phpcsFile, $commentStart);
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    protected function processVariable(File $phpcsFile, $stackPtr): void
    {
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    protected function processVariableInString(File $phpcsFile, $stackPtr): void
    {
    }

    /**
     * @param File $phpcsFile
     * @param int  $commentStart
     */
    private function processWhitespace(File $phpcsFile, int $commentStart): void
    {
        $tokens = $phpcsFile->getTokens();
        $before = $phpcsFile->findPrevious(T_WHITESPACE, $commentStart - 1, null, true);

        $startLine = $tokens[$commentStart]['line'];
        $prevLine = $tokens[$before]['line'];

        $found = $startLine - $prevLine - 1;

        // Skip for class opening
        if ($found < 1 && T_OPEN_CURLY_BRACKET !== $tokens[$before]['code']) {
            if ($found < 0) {
                $found = 0;
            }

            $fix = $phpcsFile->addFixableError(
                'Expected 1 blank line before docblock; %s found',
                $commentStart,
                'SpacingBeforeDocblock',
                [$found]
            );

            if ($fix) {
                if ($found > 1) {
                    $phpcsFile->fixer->beginChangeset();

                    for ($i = $before + 1; $i < $commentStart - 1; $i++) {
                        $phpcsFile->fixer->replaceToken($i, '');
                    }

                    $phpcsFile->fixer->endChangeset();
                } else {
                    // Try and maintain indentation.
                    if (T_WHITESPACE === $tokens[$commentStart - 1]['code']) {
                        $phpcsFile->fixer->addNewlineBefore($commentStart - 1);
                    } else {
                        $phpcsFile->fixer->addNewlineBefore($commentStart);
                    }
                }
            }
        }
    }
}
