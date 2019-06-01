<?php

namespace TwigCS\Sniff\Standard;

use \Exception;
use TwigCS\Sniff\AbstractSniff;
use TwigCS\Token\Token;

/**
 * Disallow keeping commented code.
 *
 * This will be triggered if `{{` or `{%` is found inside a comment.
 */
class ForbidCommentedCodeSniff extends AbstractSniff
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
        if ($this->isTokenMatching($token, Token::COMMENT_START_TYPE)) {
            $i = $tokenPosition;
            $found = false;
            while (!$this->isTokenMatching($tokens[$i], Token::COMMENT_END_TYPE)
                || $this->isTokenMatching($tokens[$i], Token::EOF_TYPE)
            ) {
                if ($this->isTokenMatching($tokens[$i], Token::TEXT_TYPE, '{{')
                    || $this->isTokenMatching($tokens[$i], Token::TEXT_TYPE, '{%')
                ) {
                    $found = true;

                    break;
                }

                ++$i;
            }

            if ($found) {
                $this->addMessage(
                    $this::MESSAGE_TYPE_WARNING,
                    'Probable commented code found; keeping commented code is not advised',
                    $token
                );
            }
        }

        return $token;
    }
}
