<?php

namespace TwigCS\Sniff;

use \Exception;
use TwigCS\Report\Report;
use TwigCS\Report\SniffViolation;
use TwigCS\Token\Token;

/**
 * Base for all sniff.
 */
abstract class AbstractSniff implements SniffInterface
{
    /**
     * When process is called, it will fill this report with the potential violations.
     *
     * @var Report
     */
    protected $report;

    /**
     * @var array
     */
    protected $messages;

    public function __construct()
    {
        $this->messages = [];
        $this->report   = null;
    }

    /**
     * @param Report $report
     *
     * @return self
     */
    public function enable(Report $report)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * @return self
     */
    public function disable()
    {
        $this->report = null;

        return $this;
    }

    /**
     * @return Report
     *
     * @throws Exception
     */
    public function getReport()
    {
        if (null === $this->report) {
            throw new Exception('Sniff is disabled!');
        }

        return $this->report;
    }

    /**
     * Helper method to match a token of a given type and value.
     *
     * @param Token  $token
     * @param int    $type
     * @param string $value
     *
     * @return bool
     */
    public function isTokenMatching(Token $token, int $type, string $value = null)
    {
        return $token->getType() === $type && (null === $value || $token->getValue() === $value);
    }

    /**
     * Adds a violation to the current report for the given token.
     *
     * @param int    $messageType
     * @param string $message
     * @param Token  $token
     *
     * @return self
     *
     * @throws Exception
     */
    public function addMessage(int $messageType, string $message, Token $token)
    {
        $sniffViolation = new SniffViolation(
            $messageType,
            $message,
            $token->getFilename(),
            $token->getLine()
        );
        $sniffViolation->setLinePosition($token->getPosition());

        $this->getReport()->addMessage($sniffViolation);

        return $this;
    }

    /**
     * @param Token $token
     *
     * @return string
     */
    public function stringifyValue(Token $token)
    {
        if ($token->getType() === Token::STRING_TYPE) {
            return $token->getValue();
        }

        return '\''.$token->getValue().'\'';
    }
}
