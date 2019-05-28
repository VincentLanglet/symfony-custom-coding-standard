<?php

namespace SymfonyCustom\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Throws errors if comments are not grouped by type with one blank line between them.
 */
class DocCommentGroupSameTypeSniff implements Sniff
{
    /**
     * A list of PHPDoc tags that are checked.
     *
     * @var array
     */
    public $tags = [
        '@api',
        '@author',
        '@category',
        '@copyright',
        '@covers',
        '@dataProvider',
        '@deprecated',
        '@example',
        '@filesource',
        '@global',
        '@ignore',
        '@internal',
        '@license',
        '@link',
        '@method',
        '@package',
        '@param',
        '@property',
        '@property-read',
        '@property-write',
        '@required',
        '@return',
        '@see',
        '@since',
        '@source',
        '@subpackage',
        '@throws',
        '@todo',
        '@uses',
        '@var',
        '@version',
    ];

    /**
     * A list of tokenizers this sniff supports.
     *
     * @return array
     */
    public function register()
    {
        return [
            T_DOC_COMMENT_OPEN_TAG,
        ];
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param File $phpcsFile All the tokens found in the document.
     * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $previousType = '';
        foreach ($tokens[$stackPtr]['comment_tags'] as $commentTag) {
            $currentType = $tokens[$commentTag]['content'];
            $commentTagLine = $tokens[$commentTag]['line'];

            $previousString = $phpcsFile->findPrevious(
                T_DOC_COMMENT_STRING,
                $commentTag,
                $stackPtr
            );

            $previousTag = $phpcsFile->findPrevious(
                T_DOC_COMMENT_TAG,
                $commentTag - 1,
                $stackPtr
            );

            $previousLine = -1;
            if (false !== $previousString) {
                $previousLine = $tokens[$previousString]['line'];
                $previousElement = $previousString;
            }

            if (false !== $previousTag) {
                $previousTagLine = $tokens[$previousTag]['line'];

                if ($previousTagLine > $previousLine) {
                    $previousLine = $previousTagLine;
                    $previousElement = $previousTag;
                }
            }

            if (isset($previousElement) && $previousLine >= 0) {
                $currentIsCustom = !in_array($currentType, $this->tags);
                $previousIsCustom = ('' !== $previousType) && !in_array($previousType, $this->tags);

                if (($previousType === $currentType) || ($currentIsCustom && $previousIsCustom)) {
                    if ($previousLine !== $commentTagLine - 1) {
                        if ($previousType === $currentType) {
                            $fix = $phpcsFile->addFixableError(
                                'Expected no empty lines between annotations of the same type',
                                $commentTag,
                                'SameType'
                            );
                        } else {
                            $fix = $phpcsFile->addFixableError(
                                'Expected no empty lines between custom annotations',
                                $commentTag,
                                'CustomType'
                            );
                        }

                        if (true === $fix) {
                            $phpcsFile->fixer->beginChangeset();
                            $this->removeLines(
                                $phpcsFile,
                                $previousElement,
                                $previousLine + 1,
                                $commentTagLine - 1
                            );
                            $phpcsFile->fixer->endChangeset();
                        }
                    }
                } else {
                    if ($previousLine !== $commentTagLine - 2) {
                        $fix = $phpcsFile->addFixableError(
                            'Expected exactly one empty line between annotations of different types',
                            $commentTag,
                            'DifferentType'
                        );

                        if (true === $fix) {
                            $phpcsFile->fixer->beginChangeset();

                            if ($previousLine === $commentTagLine - 1) {
                                $firstOnLine = $phpcsFile->findFirstOnLine(
                                    [],
                                    $commentTag,
                                    true
                                );
                                $star = $phpcsFile->findNext(
                                    T_DOC_COMMENT_STAR,
                                    $firstOnLine
                                );
                                $content = $phpcsFile->getTokensAsString(
                                    $firstOnLine,
                                    $star - $firstOnLine + 1
                                );
                                $phpcsFile->fixer->addContentBefore(
                                    $firstOnLine,
                                    $content.$phpcsFile->eolChar
                                );
                            } else {
                                $this->removeLines(
                                    $phpcsFile,
                                    $previousElement,
                                    $previousLine + 2,
                                    $commentTagLine - 1
                                );
                            }
                            $phpcsFile->fixer->endChangeset();
                        }
                    }
                }
            }

            $previousType = $currentType;
        }
    }

    /**
     * Remove all tokens on lines (inclusively).
     *
     * Note: this method does not start or end changeset.
     *
     * @param File $phpcsFile File to make changes in
     * @param int  $fromPtr   Start searching tokens from here
     * @param int  $fromLine  First line to delete tokens from
     * @param int  $toLine    Last line to delete tokens from
     *
     * @return void
     */
    protected function removeLines(File $phpcsFile, $fromPtr, $fromLine, $toLine)
    {
        $tokens = $phpcsFile->getTokens();

        for ($i = $fromPtr;; $i++) {
            if ($tokens[$i]['line'] > $toLine) {
                break;
            }

            if ($fromLine <= $tokens[$i]['line']) {
                $phpcsFile->fixer->replaceToken($i, '');
            }
        }
    }
}
