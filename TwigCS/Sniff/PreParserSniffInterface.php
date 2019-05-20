<?php

namespace TwigCS\Sniff;

use TwigCS\Token\Token;

/**
 * Interface PreParserSniffInterface
 */
interface PreParserSniffInterface extends SniffInterface
{
    /**
     * @param Token   $token
     * @param int     $tokenPosition
     * @param Token[] $stream
     */
    public function process(Token $token, $tokenPosition, $stream);
}
