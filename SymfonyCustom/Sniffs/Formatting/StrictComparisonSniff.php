<?php

namespace SymfonyCustom\Sniffs\Formatting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Throws warning if == or != are use
 */
class StrictComparisonSniff implements Sniff
{
    /**
     * Types to replace: key is operator to replace, value is operator to replace with.
     *
     * @var array
     */
    public $operators = array(
        'T_IS_EQUAL'     => '===',
        'T_IS_NOT_EQUAL' => '!==',
    );

    /**
     * A list of tokenizers this sniff supports.
     *
     * @return array
     */
    public function register()
    {
        return array(
            T_IS_EQUAL,
            T_IS_NOT_EQUAL,
        );
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

        // This warning is fixable, but it's too dangerous to add automatically fixer
        $phpcsFile->addWarning(
            'The %s comparator is not recommended, use %s instead',
            $stackPtr,
            '',
            array(
                $tokens[$stackPtr]['content'],
                $this->operators[$tokens[$stackPtr]['type']],
            )
        );
    }
}
