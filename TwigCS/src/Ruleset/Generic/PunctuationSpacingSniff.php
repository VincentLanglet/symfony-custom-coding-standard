<?php

declare(strict_types=1);

namespace TwigCS\Ruleset\Generic;

use TwigCS\Sniff\AbstractSpacingSniff;
use TwigCS\Token\Token;

/**
 * Ensure there is no space before and after a punctuation except for ':' and ','
 */
class PunctuationSpacingSniff extends AbstractSpacingSniff
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
        if ($this->isTokenMatching($token, Token::PUNCTUATION_TYPE, [')', ']', '}', ':', '.', ',', '|'])) {
            return 0;
        }

        return null;
    }

    /**
     * @param int     $tokenPosition
     * @param Token[] $tokens
     *
     * @return int|null
     */
    protected function shouldHaveSpaceAfter(int $tokenPosition, array $tokens): ?int
    {
        $token = $tokens[$tokenPosition];
        if ($this->isTokenMatching($token, Token::PUNCTUATION_TYPE, [':', ','])) {
            return 1;
        }

        if ($this->isTokenMatching($token, Token::PUNCTUATION_TYPE, ['(', '[', '{', '.', '|'])) {
            return 0;
        }

        return null;
    }
}
