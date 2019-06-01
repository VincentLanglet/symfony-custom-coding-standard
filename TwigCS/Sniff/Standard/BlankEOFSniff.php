<?php

namespace TwigCS\Sniff\Standard;

use \Exception;
use TwigCS\Sniff\AbstractSniff;
use TwigCS\Token\Token;

/**
 * Ensure that files ends with one blank line.
 */
class BlankEOFSniff extends AbstractSniff
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
        if ($this->isTokenMatching($token, Token::EOF_TYPE)) {
            $i = 0;
            while (isset($tokens[$tokenPosition - ($i + 1)])
                && $this->isTokenMatching($tokens[$tokenPosition - ($i + 1)], Token::EOL_TYPE)
            ) {
                ++$i;
            }

            if (1 !== $i) {
                // Either 0 or 2+ blank lines.
                $this->addMessage(
                    $this::MESSAGE_TYPE_ERROR,
                    sprintf('A file must end with 1 blank line; found %d', $i),
                    $token
                );
            }
        }

        return $token;
    }
}
