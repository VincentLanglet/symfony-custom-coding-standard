<?php

declare(strict_types=1);

namespace TwigCS\Ruleset\Generic;

use TwigCS\Sniff\AbstractSpacingSniff;
use TwigCS\Token\Token;

/**
 * Ensure there is one space before and after an operator
 */
class OperatorSpacingSniff extends AbstractSpacingSniff
{
    /**
     * @param int     $tokenPosition
     * @param Token[] $tokens
     *
     * @return int|null
     */
    protected function shouldHaveSpaceBefore(int $tokenPosition, array $tokens): ?int
    {
        $token = $tokens[$tokenPosition];

        $isMinus = $this->isTokenMatching($token, Token::OPERATOR_TYPE, '-');
        $isPlus = $this->isTokenMatching($token, Token::OPERATOR_TYPE, '+');

        if ($isMinus || $isPlus) {
            return $this->isUnary($tokenPosition, $tokens) ? null : 1;
        }

        return $this->isTokenMatching($token, Token::OPERATOR_TYPE) && '..' !== $token->getValue() ? 1 : null;
    }

    /**
     * @param int     $tokenPosition
     * @param Token[] $tokens
     *
     * @return bool
     */
    protected function shouldHaveSpaceAfter(int $tokenPosition, array $tokens): ?int
    {
        $token = $tokens[$tokenPosition];

        $isMinus = $this->isTokenMatching($token, Token::OPERATOR_TYPE, '-');
        $isPlus = $this->isTokenMatching($token, Token::OPERATOR_TYPE, '+');

        if ($isMinus || $isPlus) {
            return $this->isUnary($tokenPosition, $tokens) ? 0 : 1;
        }

        return $this->isTokenMatching($token, Token::OPERATOR_TYPE) && '..' !== $token->getValue() ? 1 : null;
    }

    /**
     * @param int   $tokenPosition
     * @param array $tokens
     *
     * @return bool
     */
    private function isUnary(int $tokenPosition, array $tokens): bool
    {
        $previous = $this->findPrevious(Token::EMPTY_TOKENS, $tokens, $tokenPosition - 1, true);
        if (false === $previous) {
            return true;
        }

        $previousToken = $tokens[$previous];
        if ($this->isTokenMatching($previousToken, Token::OPERATOR_TYPE)) {
            // {{ 1 * -2 }}
            return true;
        }

        if ($this->isTokenMatching($previousToken, Token::VAR_START_TYPE)) {
            // {{ -2 }}
            return true;
        }

        if ($this->isTokenMatching($previousToken, Token::PUNCTUATION_TYPE, ['(', '[', ':', ','])) {
            // {{ 1 + (-2) }}
            return true;
        }

        if ($this->isTokenMatching($previousToken, Token::BLOCK_TAG_TYPE)) {
            // {% if -2 ... %}
            return true;
        }

        return false;
    }
}
