<?php

class DocCommentTagSpacingUnitTest
{
    /**
     * Short description of the member variable.
     *
     * Long description of member variable. Can span over multiple
     * lines and can have multiple paragraphs.
     *
     *@var array
     *
     *  @see otherFunctions()
     * @see       anotherFunctions()
     */
    public $variableName = array();

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("libelle")
     */
    private $label;

    /**
     * T_VARIABLE check, var in string and in function.
     *
     *@param integer $var1 First variable.
     * @param  integer $var2 Second variable.
     *
     *  @return integer
     */
    protected function checkFunction($var1, $var2)
    {
        /**@var int $var3 */
        $var3 = $var1 + $var2;

        return $var3;
    }
}
