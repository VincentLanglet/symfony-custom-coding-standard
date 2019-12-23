<?php

declare(strict_types=1);

namespace SymfonyCustom\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Throws errors if forbidden tags are met.
 */
class DocCommentForbiddenTagsSniff implements Sniff
{
    /**
     * @var array
     */
    public $forbiddenTags = ['@package', '@subpackage'];

    /**
     * @return array
     */
    public function register(): array
    {
        return [T_DOC_COMMENT_TAG];
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        if (in_array($tokens[$stackPtr]['content'], $this->forbiddenTags)) {
            $phpcsFile->addError(
                'The %s annotation is forbidden to use',
                $stackPtr,
                '',
                [$tokens[$stackPtr]['content']]
            );
        }
    }
}
