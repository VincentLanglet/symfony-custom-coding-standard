<?php

namespace TwigCS\Sniff\Standard;

use \Exception;
use TwigCS\Sniff\AbstractSniff;
use TwigCS\Token\Token;

/**
 * Ensure there is one space before and after a delimiter {{, {%, {#, }}, %} and #}
 */
class DelimiterSpacingSniff extends AbstractSniff
{
    /**
     * @param Token   $token
     * @param int     $tokenPosition
     * @param Token[] $tokens
     *
     * @return Token
     *
     * @throws Exception
     */
    public function process(Token $token, $tokenPosition, $tokens)
    {
        if ($this->isTokenMatching($token, Token::VAR_START_TYPE)
            || $this->isTokenMatching($token, Token::BLOCK_START_TYPE)
            || $this->isTokenMatching($token, Token::COMMENT_START_TYPE)
        ) {
            $this->processStart($token, $tokenPosition, $tokens);
        }

        if ($this->isTokenMatching($token, Token::VAR_END_TYPE)
            || $this->isTokenMatching($token, Token::BLOCK_END_TYPE)
            || $this->isTokenMatching($token, Token::COMMENT_END_TYPE)
        ) {
            $this->processEnd($token, $tokenPosition, $tokens);
        }

        return $token;
    }

    /**
     * @param Token   $token
     * @param int     $tokenPosition
     * @param Token[] $tokens
     *
     * @throws Exception
     */
    public function processStart(Token $token, $tokenPosition, $tokens)
    {
        // Ignore new line
        if ($this->isTokenMatching($tokens[$tokenPosition + 1], Token::EOL_TYPE)) {
            return;
        }

        if ($this->isTokenMatching($tokens[$tokenPosition + 1], Token::WHITESPACE_TYPE)) {
            $count = strlen($tokens[$tokenPosition + 1]->getValue());
        } else {
            $count = 0;
        }

        if (1 !== $count) {
            $this->addMessage(
                $this::MESSAGE_TYPE_ERROR,
                sprintf('Expecting 1 whitespace after "%s"; found %d', $token->getValue(), $count),
                $token
            );
        }
    }

    /**
     * @param Token   $token
     * @param int     $tokenPosition
     * @param Token[] $tokens
     *
     * @throws Exception
     */
    public function processEnd(Token $token, $tokenPosition, $tokens)
    {
        // Ignore new line
        if ($this->isTokenMatching($tokens[$tokenPosition - 1], Token::EOL_TYPE)) {
            return;
        }

        if ($this->isTokenMatching($tokens[$tokenPosition - 1], Token::WHITESPACE_TYPE)) {
            $count = strlen($tokens[$tokenPosition - 1]->getValue());
        } else {
            $count = 0;
        }

        if (1 !== $count) {
            $this->addMessage(
                $this::MESSAGE_TYPE_ERROR,
                sprintf('Expecting 1 whitespace before "%s"; found %d', $token->getValue(), $count),
                $token
            );
        }
    }
}
