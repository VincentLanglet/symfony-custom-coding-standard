<?php

namespace TwigCS\Sniff;

use Exception;
use TwigCS\Token\Token;

/**
 * Ensure there is one space before or after some tokens
 */
abstract class AbstractSpacingSniff extends AbstractSniff
{
    /**
     * @param int     $tokenPosition
     * @param Token[] $tokens
     *
     * @throws Exception
     */
    public function process(int $tokenPosition, array $tokens): void
    {
        $token = $tokens[$tokenPosition];

        if ($this->shouldHaveSpaceAfter($token)) {
            $this->checkSpaceAfter($tokenPosition, $tokens);
        }

        if ($this->shouldHaveSpaceBefore($token)) {
            $this->checkSpaceBefore($tokenPosition, $tokens);
        }
    }

    /**
     * @param Token $token
     *
     * @return bool
     */
    abstract protected function shouldHaveSpaceAfter(Token $token): bool;

    /**
     * @param Token $token
     *
     * @return bool
     */
    abstract protected function shouldHaveSpaceBefore(Token $token): bool;

    /**
     * @param int     $tokenPosition
     * @param Token[] $tokens
     *
     * @throws Exception
     */
    protected function checkSpaceAfter(int $tokenPosition, array $tokens): void
    {
        $token = $tokens[$tokenPosition];

        // Ignore new line
        $next = $this->findNext(Token::WHITESPACE_TYPE, $tokens, $tokenPosition + 1, true);
        if ($this->isTokenMatching($tokens[$next], Token::EOL_TYPE)) {
            return;
        }

        if ($this->isTokenMatching($tokens[$tokenPosition + 1], Token::WHITESPACE_TYPE)) {
            $count = strlen($tokens[$tokenPosition + 1]->getValue());
        } else {
            $count = 0;
        }

        if (1 !== $count) {
            $fix = $this->addFixableError(
                sprintf('Expecting 1 whitespace after "%s"; found %d', $token->getValue(), $count),
                $token
            );

            if ($fix) {
                if (0 === $count) {
                    $this->fixer->addContent($tokenPosition, ' ');
                } else {
                    $this->fixer->replaceToken($tokenPosition + 1, ' ');
                }
            }
        }
    }

    /**
     * @param int     $tokenPosition
     * @param Token[] $tokens
     *
     * @throws Exception
     */
    protected function checkSpaceBefore(int $tokenPosition, array $tokens): void
    {
        $token = $tokens[$tokenPosition];

        // Ignore new line
        $previous = $this->findPrevious(Token::WHITESPACE_TYPE, $tokens, $tokenPosition - 1, true);
        if ($this->isTokenMatching($tokens[$previous], Token::EOL_TYPE)) {
            return;
        }

        if ($this->isTokenMatching($tokens[$tokenPosition - 1], Token::WHITESPACE_TYPE)) {
            $count = strlen($tokens[$tokenPosition - 1]->getValue());
        } else {
            $count = 0;
        }

        if (1 !== $count) {
            $fix = $this->addFixableError(
                sprintf('Expecting 1 whitespace before "%s"; found %d', $token->getValue(), $count),
                $token
            );

            if ($fix) {
                if (0 === $count) {
                    $this->fixer->addContentBefore($tokenPosition, ' ');
                } else {
                    $this->fixer->replaceToken($tokenPosition - 1, ' ');
                }
            }
        }
    }
}
