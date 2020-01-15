<?php

declare(strict_types=1);

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
     * @var Report|null
     */
    protected $report;

    /**
     * @var Fixer|null
     */
    protected $fixer;

    /**
     * @param Report $report
     */
    public function enableReport(Report $report): void
    {
        $this->report = $report;
    }

    /**
     * @param Fixer $fixer
     */
    public function enableFixer(Fixer $fixer): void
    {
        $this->fixer = $fixer;
    }

    public function disable(): void
    {
        $this->report = null;
        $this->fixer = null;
    }

    /**
     * @param Token        $token
     * @param int|array    $type
     * @param string|array $value
     *
     * @return bool
     */
    public function isTokenMatching(Token $token, $type, $value = []): bool
    {
        if (!is_array($type)) {
            $type = [$type];
        }
        if (!is_array($value)) {
            $value = [$value];
        }

        return in_array($token->getType(), $type) && ([] === $value || in_array($token->getValue(), $value));
    }

    /**
     * @param int|array $type
     * @param array     $tokens
     * @param int       $start
     * @param bool      $exclude
     *
     * @return int|false
     */
    public function findNext($type, array $tokens, int $start, bool $exclude = false)
    {
        $i = 0;

        while (isset($tokens[$start + $i]) && $exclude === $this->isTokenMatching($tokens[$start + $i], $type)) {
            $i++;
        }

        if (!isset($tokens[$start + $i])) {
            return false;
        }

        return $start + $i;
    }

    /**
     * @param int|array $type
     * @param array     $tokens
     * @param int       $start
     * @param bool      $exclude
     *
     * @return int|false
     */
    public function findPrevious($type, array $tokens, int $start, bool $exclude = false)
    {
        $i = 0;

        while (isset($tokens[$start - $i]) && $exclude === $this->isTokenMatching($tokens[$start - $i], $type)) {
            $i++;
        }

        if (!isset($tokens[$start - $i])) {
            return false;
        }

        return $start - $i;
    }

    /**
     * @param string $message
     * @param Token  $token
     *
     * @throws Exception
     */
    public function addWarning(string $message, Token $token): void
    {
        $this->addMessage(Report::MESSAGE_TYPE_WARNING, $message, $token);
    }

    /**
     * @param string $message
     * @param Token  $token
     *
     * @throws Exception
     */
    public function addError(string $message, Token $token): void
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
    public function addFixableWarning(string $message, Token $token): bool
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
    public function addFixableError(string $message, Token $token): bool
    {
        return $this->addFixableMessage(Report::MESSAGE_TYPE_ERROR, $message, $token);
    }

    /**
     * @param Token $token
     *
     * @return string|null
     */
    public function stringifyValue(Token $token): ?string
    {
        if ($token->getType() === Token::STRING_TYPE) {
            return $token->getValue();
        }

        return '\''.$token->getValue().'\'';
    }

    /**
     * @param array $stream
     */
    public function processFile(array $stream): void
    {
        foreach ($stream as $index => $token) {
            $this->process($index, $stream);
        }
    }

    /**
     * @param int     $tokenPosition
     * @param Token[] $stream
     */
    abstract protected function process(int $tokenPosition, array $stream): void;

    /**
     * @param int    $messageType
     * @param string $message
     * @param Token  $token
     *
     * @throws Exception
     */
    private function addMessage(int $messageType, string $message, Token $token): void
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
    private function addFixableMessage(int $messageType, string $message, Token $token): bool
    {
        $this->addMessage($messageType, $message, $token);

        return null !== $this->fixer;
    }
}
