<?php

/**
 * Throws errors if forbidden tags are met.
 */
class Symfony3Custom_Sniffs_Commenting_DocCommentForbiddenTagsSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * A list of PHPDoc tags that are forbidden.
     *
     * @var array
     */
    public $tags = array(
        '@package',
        '@subpackage',
    );

    /**
     * A list of tokenizers this sniff supports.
     *
     * @return array
     */
    public function register()
    {
        return array(T_DOC_COMMENT_TAG);
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile All the tokens found in the document.
     * @param int                  $stackPtr  The position of the current token in
     *                                        the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        if (in_array(
            $tokens[$stackPtr]['content'],
            $this->tags
        )
        ) {
            $phpcsFile->addError(
                'The %s annotation is forbidden to use',
                $stackPtr,
                '',
                array($tokens[$stackPtr]['content'])
            );
        }
    }
}
