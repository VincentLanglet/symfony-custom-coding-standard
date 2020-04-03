<?php

declare(strict_types=1);

namespace SymfonyCustom\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\AbstractVariableSniff;
use SymfonyCustom\Helpers\SniffHelper;

/**
 * Parses and verifies the variable doc comment.
 */
class VariableCommentSniff extends AbstractVariableSniff
{
    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     *
     * @return void
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

        [$type, $space, $description] = SniffHelper::parseTypeHint($tokens[$string]['content']);
        if (preg_match('/^\$\S+/', $description)) {
            $fix = $phpcsFile->addFixableError(
                '@var annotations should not contain variable name',
                $foundVar,
                'NamedVar'
            );

            if ($fix) {
                $description = preg_replace('/^\$\S+/', '', $description);
                if ('' !== $description) {
                    $phpcsFile->fixer->replaceToken($string, $type.$space.$description);
                } else {
                    $phpcsFile->fixer->replaceToken($string, $type);
                }
            }
        }
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     *
     * @return void
     */
    protected function processVariable(File $phpcsFile, $stackPtr): void
    {
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     *
     * @return void
     */
    protected function processVariableInString(File $phpcsFile, $stackPtr): void
    {
    }
}
