<?php

declare(strict_types=1);

namespace SymfonyCustom\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SymfonyCustom\Sniffs\SniffHelper;

/**
 * Throws errors if comments are not grouped by type with one blank line between them.
 */
class DocCommentGroupSameTypeSniff implements Sniff
{
    /**
     * @return array
     */
    public function register(): array
    {
        return [T_DOC_COMMENT_OPEN_TAG];
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    public function process(File $phpcsFile, $stackPtr): void
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
                $currentIsCustom = !in_array($currentType, SniffHelper::TAGS);
                $previousIsCustom = '' !== $previousType
                    && !in_array($previousType, SniffHelper::TAGS);

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

                        if ($fix) {
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

                        if ($fix) {
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
     * @param File $phpcsFile
     * @param int  $fromPtr
     * @param int  $fromLine
     * @param int  $toLine
     */
    private function removeLines(File $phpcsFile, int $fromPtr, int $fromLine, int $toLine): void
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
