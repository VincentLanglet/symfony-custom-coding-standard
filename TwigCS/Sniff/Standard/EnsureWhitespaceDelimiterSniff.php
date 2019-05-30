<?php

namespace TwigCS\Sniff\Standard;

use \Exception;
use TwigCS\Sniff\AbstractPreParserSniff;
use TwigCS\Token\Token;

/**
 * Ensure there is one space before and after a delimiter {{, {%, {#, }}, %} and #}
 */
class EnsureWhitespaceDelimiterSniff extends AbstractPreParserSniff
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
        $offset = 1;
        while ($this->isTokenMatching($tokens[$tokenPosition + $offset], Token::WHITESPACE_TYPE)) {
            ++$offset;
        }

        // Ignore new line
        if ($this->isTokenMatching($tokens[$tokenPosition + $offset], Token::EOL_TYPE)) {
            return;
        }

        $count = $offset - 1;
        if (1 !== $count) {
            $this->addMessage(
                $this::MESSAGE_TYPE_ERROR,
                sprintf('Expecting 1 whitespace AFTER start of expression eg. "{{" or "{%%"; found %d', $count),
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
        $offset = 1;
        while ($this->isTokenMatching($tokens[$tokenPosition - $offset], Token::WHITESPACE_TYPE)) {
            ++$offset;
        }

        // Ignore new line
        if ($this->isTokenMatching($tokens[$tokenPosition - $offset], Token::EOL_TYPE)) {
            return;
        }

        $count = $offset - 1;
        if (1 !== $count) {
            $this->addMessage(
                $this::MESSAGE_TYPE_ERROR,
                sprintf('Expecting 1 whitespace BEFORE end of expression eg. "}}" or "%%}"; found %d', $count),
                $token
            );
        }
    }
}
