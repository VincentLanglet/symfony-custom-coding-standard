<?php

namespace TwigCS\Ruleset\Generic;

use TwigCS\Sniff\AbstractSpacingSniff;
use TwigCS\Token\Token;

/**
 * Ensure there is one space before and after an operator
 */
class OperatorSpacingSniff extends AbstractSpacingSniff
{
    /**
     * @param Token $token
     *
     * @return bool
     */
    protected function shouldHaveSpaceBefore(Token $token)
    {
        return $this->isTokenMatching($token, Token::OPERATOR_TYPE);
    }

    /**
     * @param Token $token
     *
     * @return bool
     */
    protected function shouldHaveSpaceAfter(Token $token)
    {
        return $this->isTokenMatching($token, Token::OPERATOR_TYPE);
    }
}
