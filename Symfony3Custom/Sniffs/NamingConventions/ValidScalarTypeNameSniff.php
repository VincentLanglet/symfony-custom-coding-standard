<?php

/**
 * Throws errors if scalar type name are not valid.
 */
class Symfony3Custom_Sniffs_NamingConventions_ValidScalarTypeNameSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Types to replace: key is type to replace, value is type to replace with.
     *
     * @var array
     */
    public $types = array(
        'boolean' => 'bool',
        'double' => 'float',
        'integer' => 'int',
        'real' => 'float',
    );

    /**
     * A list of tokenizers this sniff supports.
     *
     * @return array
     */
    public function register()
    {
        $tokens = PHP_CodeSniffer_Tokens::$castTokens;
        $tokens[] = T_DOC_COMMENT_OPEN_TAG;

        return $tokens;
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
        if (T_DOC_COMMENT_OPEN_TAG === $tokens[$stackPtr]['code']) {
            $this->validateDocComment($phpcsFile, $stackPtr);
        } else {
            $this->validateCast($phpcsFile, $stackPtr);
        }
    }

    /**
     * Validates PHPDoc comment.
     *
     * @param PHP_CodeSniffer_File $phpcsFile File to process
     * @param int                  $stackPtr  Position of PHPDoc comment to validate
     *
     * @return void
     */
    protected function validateDocComment(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        foreach ($tokens[$stackPtr]['comment_tags'] as $commentTag) {
            if (in_array(
                $tokens[$commentTag]['content'],
                array('@param', '@return', '@var')
            )
            ) {
                $docString = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $commentTag);
                if (false !== $docString) {
                    $stringParts = explode(' ', $tokens[$docString]['content']);
                    $typeName = $stringParts[0];
                    $this->validateTypeName($phpcsFile, $docString, $typeName);
                }
            }
        }
    }

    /**
     * Validates cast operator.
     *
     * @param PHP_CodeSniffer_File $phpcsFile File to process
     * @param int                  $stackPtr  Position of cast to validate
     *
     * @return void
     */
    protected function validateCast(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        preg_match('/^\(\s*(\S+)\s*\)$/', $tokens[$stackPtr]['content'], $matches);
        $typeName = $matches[1];

        $this->validateTypeName($phpcsFile, $stackPtr, $typeName);
    }

    /**
     * Validates type name.
     *
     * @param PHP_CodeSniffer_File $phpcsFile File to process
     * @param int                  $stackPtr  Position, where error will be raised
     * @param string               $typeName  Type name to validate
     *
     * @return void
     */
    protected function validateTypeName(
        PHP_CodeSniffer_File $phpcsFile,
        $stackPtr,
        $typeName
    ) {
        $validTypeName = $this->getValidTypeName($typeName);

        if (false !== $validTypeName) {
            $needFix = $phpcsFile->addFixableError(
                'For type-hinting in PHPDocs and casting, use %s instead of %s',
                $stackPtr,
                '',
                array($validTypeName, $typeName)
            );
            if ($needFix) {
                $tokens = $phpcsFile->getTokens();
                $phpcsFile->fixer->beginChangeset();
                $newContent = str_replace(
                    $typeName,
                    $validTypeName,
                    $tokens[$stackPtr]['content']
                );
                $phpcsFile->fixer->replaceToken($stackPtr, $newContent);
                $phpcsFile->fixer->endChangeset();
            }
        }
    }

    /**
     * Returns valid type name.
     *
     * @param string $typeName Invalid type name.
     *
     * @return string|bool Valid type name if provided one is invalid,
     *                     false otherwise.
     */
    protected function getValidTypeName($typeName)
    {
        $typeName = strtolower($typeName);
        if (isset($this->types[$typeName])) {
            return $this->types[$typeName];
        }

        return false;
    }
}
