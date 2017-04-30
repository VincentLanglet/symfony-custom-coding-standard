<?php

class VariableCommentUnitTest
{
    /**
     * Short description of the member variable.
     *
     * Long description of member variable. Can span over multiple
     * lines and can have multiple paragraphs.
     *
     * @var array
     * @see otherFunctions()
     * @see anotherFunctions()
     */
     public $variableName = array();


     // Not "/**" style comment.
     //
     // @var   string
     private  $_incorrectCommentStyle = '';


     protected $noComment = '';


    /**
     *
     * Extra newline before short comment.
     *
     * @var string
     */
     public $extraNewlineBeforeShort = '';


    /**
     * Extra newline between short description.
     *
     *
     *
     * Long description.
     *
     * @var string
     */
     private $_extraNewlineBetween = '';


    /**
     * Extra newline before tags.
     *
     * Long description,
     * with two var tags.
     *
     *
     * @var string
     * @var array
     */
     protected $extraNewlineBeforeTags = '';


    /**
     * No newline before tags, var tag missing.
     * @see otherFunctions()
     */
     protected $noNewlineBeforeTags = '';


    /**
     * Short comment that spans multiple
     * lines and does not end with a full stop
     * also var type is missing
     *
     * @var
     */
     public $singleLineFullStopShortComment = '';


    /**
     * Incorrect tag indentation.
     *
     * @var     string
     * @see        otherFunctions()
     * @see anotherFunctions()
     * @see
     */
     public $singleLineFullStopShortComment = '';


    /**
     * Unknown summary tag
     *
     * @var     string
     * @summary unknown tag
     */
     public $missingSinceTag = '';


    /**
     * T_VARIABLE check, without scope.
     *
     * @var string
     */
     $variableCheck = '';

    /**
     * T_VARIABLE check, var in string and in function.
     *
     * @param integer $var1 First variable.
     * @param integer $var2 Second variable.
     *
     * @return integer
     */
    protected function checkVariable($var1, $var2)
    {
        $var4 = 'A normal variable';
        $var5 = PHP_CodeSniffer_Tokens::$operators;
        echo "Printing $var1 $var2 and unknown $var3 in a double quoted  string\n";
        foreach (self::$_array as $index => $content) {
            echo $content;
        }

        return $var1;

    }


    /**
     *
     *
     */
     $emptyVarDoc = '';
    /**
     * @var int
     */
    private $noNewLine;
}
