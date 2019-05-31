<?php

namespace TwigCS\Token;

/**
 * Class Token
 */
class Token
{
    // From Twig\Token
    const EOF_TYPE                 = -1;
    const TEXT_TYPE                = 0;
    const BLOCK_START_TYPE         = 1;
    const VAR_START_TYPE           = 2;
    const BLOCK_END_TYPE           = 3;
    const VAR_END_TYPE             = 4;
    const NAME_TYPE                = 5;
    const NUMBER_TYPE              = 6;
    const STRING_TYPE              = 7;
    const OPERATOR_TYPE            = 8;
    const PUNCTUATION_TYPE         = 9;
    const INTERPOLATION_START_TYPE = 10;
    const INTERPOLATION_END_TYPE   = 11;
    const ARROW_TYPE               = 12;
    // New constants
    const WHITESPACE_TYPE          = 13;
    const TAB_TYPE                 = 14;
    const EOL_TYPE                 = 15;
    const COMMENT_START_TYPE       = 16;
    const COMMENT_END_TYPE         = 17;

    /**
     * @var int
     */
    private $type;

    /**
     * @var int
     */
    private $line;

    /**
     * @var int
     */
    private $position;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var string|null
     */
    private $value;

    /**
     * @param int         $type
     * @param int         $line
     * @param int         $position
     * @param string      $filename
     * @param string|null $value
     */
    public function __construct($type, $line, $position, $filename, $value = null)
    {
        $this->type = $type;
        $this->line = $line;
        $this->position = $position;
        $this->filename = $filename;
        $this->value = $value;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }
}
