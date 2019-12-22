<?php

namespace SymfonyCustom\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Throws errors if forbidden tags are met.
 */
class DocCommentForbiddenTagsSniff implements Sniff
{
    /**
     * A list of PHPDoc tags that are forbidden.
     *
     * @var array
     */
    public $forbiddenTags = ['@package', '@subpackage'];

    /**
     * A list of tokenizers this sniff supports.
     *
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
