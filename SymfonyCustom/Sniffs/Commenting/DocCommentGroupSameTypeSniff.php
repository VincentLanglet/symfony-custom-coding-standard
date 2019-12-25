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

        $typeSeen = [];
        $previousTag = false;
        $previousIsCustom = false;

        foreach ($tokens[$stackPtr]['comment_tags'] as $commentTag) {
            $currentType = $tokens[$commentTag]['content'];
            $currentIsCustom = !in_array($currentType, SniffHelper::TAGS);
            $isNewType = !in_array($currentType, $typeSeen);

            $commentTagLine = $tokens[$commentTag]['line'];

            $previousString = $phpcsFile->findPrevious(T_DOC_COMMENT_STRING, $commentTag, $stackPtr);
            $previousLine = -1;

            if (false !== $previousString) {
                $previousLine = $tokens[$previousString]['line'];
                $previousElement = $previousString;
            }

            if (false !== $previousTag) {
                $previousType = $tokens[$previousTag]['content'];
                $previousTagLine = $tokens[$previousTag]['line'];

                if ($previousTagLine > $previousLine) {
                    $previousLine = $previousTagLine;
                    $previousElement = $previousTag;
                }
            } else {
                $previousType = null;
            }

            if (isset($previousElement) && $previousLine >= 0) {
                if ($previousType === $currentType) {
                    if ($previousLine !== $commentTagLine - 1) {
                        $fix = $phpcsFile->addFixableError(
                            'Expected no empty lines between annotations of the same type',
                            $commentTag,
                            'SameType'
                        );

                        if ($fix) {
                            $this->removeLines($phpcsFile, $previousElement, $previousLine + 1, $commentTagLine - 1);
                        }
                    }
                } elseif ($currentIsCustom && $previousIsCustom) {
                    if ($previousLine !== $commentTagLine - 1) {
                        $fix = $phpcsFile->addFixableError(
                            'Expected no empty lines between custom annotations',
                            $commentTag,
                            'CustomType'
                        );

                        if ($fix) {
                            $this->removeLines($phpcsFile, $previousElement, $previousLine + 1, $commentTagLine - 1);
                        }
                    }
                } elseif (!$currentIsCustom && !$isNewType) {
                    $phpcsFile->addError(
                        'Annotation of the same type should be together',
                        $commentTag,
                        'GroupSameType'
                    );
                } elseif ($previousLine !== $commentTagLine - 2) {
                    $fix = $phpcsFile->addFixableError(
                        'Expected exactly one empty line between annotations of different types',
                        $commentTag,
                        'DifferentType'
                    );

                    if ($fix) {
                        if ($previousLine === $commentTagLine - 1) {
                            $firstOnLine = $phpcsFile->findFirstOnLine([], $commentTag, true);
                            $star = $phpcsFile->findNext(T_DOC_COMMENT_STAR, $firstOnLine);
                            $content = $phpcsFile->getTokensAsString($firstOnLine, $star - $firstOnLine + 1);

                            $phpcsFile->fixer->addContentBefore($firstOnLine, $content.$phpcsFile->eolChar);
                        } else {
                            $this->removeLines($phpcsFile, $previousElement, $previousLine + 2, $commentTagLine - 1);
                        }
                    }
                }
            }

            $previousTag = $commentTag;
            $previousIsCustom = $currentIsCustom;
            if (!$currentIsCustom && $isNewType) {
                $typeSeen[] = $currentType;
            }
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

        $phpcsFile->fixer->beginChangeset();

        for ($i = $fromPtr;; $i++) {
            if ($tokens[$i]['line'] > $toLine) {
                break;
            }

            if ($fromLine <= $tokens[$i]['line']) {
                $phpcsFile->fixer->replaceToken($i, '');
            }
        }

        $phpcsFile->fixer->endChangeset();
    }
}
