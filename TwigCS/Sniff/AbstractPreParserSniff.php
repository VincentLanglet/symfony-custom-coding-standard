<?php

namespace TwigCS\Sniff;

use TwigCS\Report\SniffViolation;
use TwigCS\Token\Token;

/**
 * Base for all pre-parser sniff.
 *
 * A post parser sniff should be useful to check code formatting mainly such as:
 * whitespaces, too many blank lines or trailing commas;
 *
 * Use `AbstractPostParserSniff` for higher-order checks.
 */
abstract class AbstractPreParserSniff extends AbstractSniff implements PreParserSniffInterface
{
    /**
     * @return string
     */
    public function getType()
    {
        return $this::TYPE_PRE_PARSER;
    }

    /**
     * Helper method to match a token of a given $type and $value.
     *
     * @param Token  $token
     * @param int    $type
     * @param string $value
     *
     * @return bool
     */
    public function isTokenMatching(Token $token, $type, $value = null)
    {
        return $token->getType() === $type
            && (null === $value || (null !== $value && $token->getValue() === $value));
    }

    /**
     * Adds a violation to the current report for the given token.
     *
     * @param int    $messageType
     * @param string $message
     * @param Token  $token
     *
     * @return self
     */
    public function addMessage($messageType, $message, Token $token)
    {
        $sniffViolation = new SniffViolation($messageType, $message, $token->getLine(), $token->getFilename());
        $sniffViolation->setLinePosition($token->getPosition());

        $this->getReport()->addMessage($sniffViolation);

        return $this;
    }

    /**
     * @param Token $token
     *
     * @return string
     */
    public function stringifyValue($token)
    {
        if ($token->getType() === Token::STRING_TYPE) {
            return $token->getValue();
        }

        return '\''.$token->getValue().'\'';
    }
}
