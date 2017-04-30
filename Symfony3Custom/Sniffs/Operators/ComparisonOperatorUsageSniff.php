<?php

/**
 * A Sniff to enforce the use of IDENTICAL type operators rather than EQUAL operators.
 */
class Symfony3Custom_Sniffs_Operators_ComparisonOperatorUsageSniff implements PHP_CodeSniffer_Sniff

{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = array(
        'PHP',
        'JS',
    );

    /**
     * A list of invalid operators with their alternatives.
     *
     * @var array(int => string)
     */
    private static $_invalidOps = array(
        'PHP' => array(
            T_IS_EQUAL     => '===',
            T_IS_NOT_EQUAL => '!==',
        ),
        'JS'  => array(
            T_IS_EQUAL     => '===',
            T_IS_NOT_EQUAL => '!==',
        ),
    );


    /**
     * Registers the token types that this sniff wishes to listen to.
     *
     * @return array
     */
    public function register()
    {
        return array(
            T_IF,
            T_ELSEIF,
            T_INLINE_THEN,
            T_WHILE,
            T_FOR,
        );
    }

    /**
     * Process the tokens that this sniff is listening for.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where the token was found.
     * @param int                  $stackPtr  The position in the stack where the token
     *                                        was found.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens    = $phpcsFile->getTokens();
        $tokenizer = $phpcsFile->tokenizerType;

        if ($tokens[$stackPtr]['code'] === T_INLINE_THEN) {
            $end = $phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$emptyTokens, ($stackPtr - 1), null, true);
            if ($tokens[$end]['code'] !== T_CLOSE_PARENTHESIS) {
                // This inline IF statement does not have its condition
                // bracketed, so we need to guess where it starts.
                for ($i = ($end - 1); $i >= 0; $i--) {
                    if ($tokens[$i]['code'] === T_SEMICOLON) {
                        // Stop here as we assume it is the end
                        // of the previous statement.
                        break;
                    } else if ($tokens[$i]['code'] === T_OPEN_TAG) {
                        // Stop here as this is the start of the file.
                        break;
                    } else if ($tokens[$i]['code'] === T_CLOSE_CURLY_BRACKET) {
                        // Stop if this is the closing brace of
                        // a code block.
                        if (isset($tokens[$i]['scope_opener']) === true) {
                            break;
                        }
                    } else if ($tokens[$i]['code'] === T_OPEN_CURLY_BRACKET) {
                        // Stop if this is the opening brace of
                        // a code block.
                        if (isset($tokens[$i]['scope_closer']) === true) {
                            break;
                        }
                    }
                }

                $start = $phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($i + 1), null, true);
            } else {
                if (isset($tokens[$end]['parenthesis_opener']) === false) {
                    return;
                }

                $start = $tokens[$end]['parenthesis_opener'];
            }
        } elseif ($tokens[$stackPtr]['code'] === T_FOR) {
            if (isset($tokens[$stackPtr]['parenthesis_opener']) === false) {
                return;
            }

            $openingBracket = $tokens[$stackPtr]['parenthesis_opener'];
            $closingBracket = $tokens[$stackPtr]['parenthesis_closer'];

            $start = $phpcsFile->findNext(T_SEMICOLON, $openingBracket, $closingBracket);
            $end   = $phpcsFile->findNext(T_SEMICOLON, ($start + 1), $closingBracket);
            if ($start === false || $end === false) {
                return;
            }
        } else {
            if (isset($tokens[$stackPtr]['parenthesis_opener']) === false) {
                return;
            }

            $start = $tokens[$stackPtr]['parenthesis_opener'];
            $end   = $tokens[$stackPtr]['parenthesis_closer'];
        }

        for ($i = $start; $i <= $end; $i++) {
            $type = $tokens[$i]['code'];
            if (in_array($type, array_keys(self::$_invalidOps[$tokenizer])) === true) {
                $error = 'Operator %s prohibited; use %s instead';
                $data  = array(
                    $tokens[$i]['content'],
                    self::$_invalidOps[$tokenizer][$type],
                );
                $fix = $phpcsFile->addFixableError($error, $i, 'NotAllowed', $data);

                if ($fix === true) {
                    $phpcsFile->fixer->beginChangeset();
                    $phpcsFile->fixer->replaceToken($i, self::$_invalidOps[$tokenizer][$type]);
                    $phpcsFile->fixer->endChangeset();
                }
            }
        }
    }
}
