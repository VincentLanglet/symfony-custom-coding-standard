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
    public $tags = [
        '@package',
        '@subpackage',
    ];

    /**
     * A list of tokenizers this sniff supports.
     *
     * @return array
     */
    public function register()
    {
        return [
            T_DOC_COMMENT_TAG,
        ];
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param File $phpcsFile All the tokens found in the document.
     * @param int  $stackPtr  The position of the current token in
     *                        the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        if (in_array($tokens[$stackPtr]['content'], $this->tags)) {
            $phpcsFile->addError(
                'The %s annotation is forbidden to use',
                $stackPtr,
                '',
                [$tokens[$stackPtr]['content']]
            );
        }
    }
}
