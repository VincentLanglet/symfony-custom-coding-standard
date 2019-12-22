<?php

namespace TwigCS\Ruleset\Generic;

use TwigCS\Sniff\AbstractSpacingSniff;
use TwigCS\Token\Token;

/**
 * Ensure there is one space before {{, {%, {#, and after }}, %} and #}
 */
class DelimiterSpacingSniff extends AbstractSpacingSniff
{
    /**
     * @param Token $token
     *
     * @return bool
     */
    protected function shouldHaveSpaceBefore(Token $token): bool
    {
        return $this->isTokenMatching($token, Token::VAR_END_TYPE)
            || $this->isTokenMatching($token, Token::BLOCK_END_TYPE)
            || $this->isTokenMatching($token, Token::COMMENT_END_TYPE);
    }

    /**
     * @param Token $token
     *
     * @return bool
     */
    protected function shouldHaveSpaceAfter(Token $token): bool
    {
        return $this->isTokenMatching($token, Token::VAR_START_TYPE)
            || $this->isTokenMatching($token, Token::BLOCK_START_TYPE)
            || $this->isTokenMatching($token, Token::COMMENT_START_TYPE);
    }
}
