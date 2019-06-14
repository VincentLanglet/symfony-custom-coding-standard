<?php

namespace TwigCS\Ruleset\Generic;

use \Exception;
use TwigCS\Sniff\AbstractSniff;
use TwigCS\Token\Token;

/**
 * Ensure that files ends with one blank line.
 */
class BlankEOFSniff extends AbstractSniff
{
    /**
     * @param int     $tokenPosition
     * @param Token[] $tokens
     *
     * @return Token
     *
     * @throws Exception
     */
    public function process(int $tokenPosition, array $tokens)
    {
        $token = $tokens[$tokenPosition];

        if ($this->isTokenMatching($token, Token::EOF_TYPE)) {
            $i = 0;
            while (isset($tokens[$tokenPosition - ($i + 1)])
                && $this->isTokenMatching($tokens[$tokenPosition - ($i + 1)], Token::EOL_TYPE)
            ) {
                $i++;
            }

            if (1 !== $i) {
                // Either 0 or 2+ blank lines.
                $fix = $this->addFixableMessage(
                    $this::MESSAGE_TYPE_ERROR,
                    sprintf('A file must end with 1 blank line; found %d', $i),
                    $token
                );

                if ($fix) {
                    if (0 === $i) {
                        $this->fixer->addNewlineBefore($tokenPosition);
                    } else {
                        $this->fixer->beginChangeset();
                        while ($i > 1) {
                            $this->fixer->replaceToken($tokenPosition - $i, '');
                            $i--;
                        }
                        $this->fixer->endChangeset();
                    }
                }
            }
        }

        return $token;
    }
}
