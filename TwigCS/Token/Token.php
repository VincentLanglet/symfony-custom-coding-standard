<?php

namespace TwigCS\Token;

use Twig\Token as TwigToken;

/**
 * Class Token
 */
class Token
{
    // From Twig\Token
    const EOF_TYPE                 = TwigToken::EOF_TYPE;
    const TEXT_TYPE                = TwigToken::TEXT_TYPE;
    const BLOCK_START_TYPE         = TwigToken::BLOCK_START_TYPE;
    const VAR_START_TYPE           = TwigToken::VAR_START_TYPE;
    const BLOCK_END_TYPE           = TwigToken::BLOCK_END_TYPE;
    const VAR_END_TYPE             = TwigToken::VAR_END_TYPE;
    const NAME_TYPE                = TwigToken::NAME_TYPE;
    const NUMBER_TYPE              = TwigToken::NUMBER_TYPE;
    const STRING_TYPE              = TwigToken::STRING_TYPE;
    const OPERATOR_TYPE            = TwigToken::OPERATOR_TYPE;
    const PUNCTUATION_TYPE         = TwigToken::PUNCTUATION_TYPE;
    const INTERPOLATION_START_TYPE = TwigToken::INTERPOLATION_START_TYPE;
    const INTERPOLATION_END_TYPE   = TwigToken::INTERPOLATION_END_TYPE;
    const ARROW_TYPE               = TwigToken::ARROW_TYPE;
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
