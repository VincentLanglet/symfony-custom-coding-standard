<?php

namespace TwigCS\Sniff;

use Exception;
use TwigCS\Report\Report;
use TwigCS\Report\SniffViolation;
use TwigCS\Runner\Fixer;
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
    protected $report = null;

    /**
     * @var Fixer
     */
    protected $fixer = null;

    /**
     * @param Report $report
     */
    public function enableReport(Report $report)
    {
        $this->report = $report;
    }

    /**
     * @param Fixer $fixer
     */
    public function enableFixer(Fixer $fixer)
    {
        $this->fixer = $fixer;
    }

    public function disable()
    {
        $this->report = null;
        $this->fixer = null;
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
     * @param int   $type
     * @param array $tokens
     * @param int   $start
     * @param bool  $exclude
     *
     * @return int
     */
    public function findNext(int $type, array $tokens, int $start, bool $exclude = false)
    {
        $i = 0;

        while (isset($tokens[$start + $i]) && $exclude === $this->isTokenMatching($tokens[$start + $i], $type)) {
            $i++;
        }

        return $start + $i;
    }

    /**
     * @param int   $type
     * @param array $tokens
     * @param int   $start
     * @param bool  $exclude
     *
     * @return int
     */
    public function findPrevious(int $type, array $tokens, int $start, bool $exclude = false)
    {
        $i = 0;

        while (isset($tokens[$start - $i]) && $exclude === $this->isTokenMatching($tokens[$start - $i], $type)) {
            $i++;
        }

        return $start - $i;
    }

    /**
     * @param string $message
     * @param Token  $token
     *
     * @throws Exception
     */
    public function addWarning(string $message, Token $token)
    {
        $this->addMessage(Report::MESSAGE_TYPE_WARNING, $message, $token);
    }

    /**
     * @param string $message
     * @param Token  $token
     *
     * @throws Exception
     */
    public function addError(string $message, Token $token)
    {
        $this->addMessage(Report::MESSAGE_TYPE_ERROR, $message, $token);
    }

    /**
     * @param string $message
     * @param Token  $token
     *
     * @return bool
     *
     * @throws Exception
     */
    public function addFixableWarning(string $message, Token $token)
    {
        return $this->addFixableMessage(Report::MESSAGE_TYPE_WARNING, $message, $token);
    }

    /**
     * @param string $message
     * @param Token  $token
     *
     * @return bool
     *
     * @throws Exception
     */
    public function addFixableError(string $message, Token $token)
    {
        return $this->addFixableMessage(Report::MESSAGE_TYPE_ERROR, $message, $token);
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

    /**
     * @param array $stream
     */
    public function processFile(array $stream)
    {
        foreach ($stream as $index => $token) {
            $this->process($index, $stream);
        }
    }

    /**
     * @param int     $tokenPosition
     * @param Token[] $stream
     */
    abstract protected function process(int $tokenPosition, array $stream);

    /**
     * @param int    $messageType
     * @param string $message
     * @param Token  $token
     *
     * @throws Exception
     */
    private function addMessage(int $messageType, string $message, Token $token)
    {
        if (null === $this->report) {
            if (null !== $this->fixer) {
                // We are fixing the file, ignore this
                return;
            }

            throw new Exception('Sniff is disabled!');
        }

        $sniffViolation = new SniffViolation(
            $messageType,
            $message,
            $token->getFilename(),
            $token->getLine()
        );
        $sniffViolation->setLinePosition($token->getPosition());

        $this->report->addMessage($sniffViolation);
    }

    /**
     * @param int    $messageType
     * @param string $message
     * @param Token  $token
     *
     * @return bool
     *
     * @throws Exception
     */
    private function addFixableMessage(int $messageType, string $message, Token $token)
    {
        $this->addMessage($messageType, $message, $token);

        return null !== $this->fixer;
    }
}
