<?php

namespace TwigCS\Report;

use TwigCS\Sniff\SniffInterface;

/**
 * Wrapper class that represents a violation to a sniff with context.
 */
class SniffViolation
{
    /**
     * Level of the message among `notice`, `warning`, `error`
     *
     * @var int
     */
    protected $level;

    /**
     * Text message associated with the violation.
     *
     * @var string
     */
    protected $message;

    /**
     * Line number for the violation.
     *
     * @var int
     */
    protected $line;

    /**
     * Position of the violation on the current line.
     *
     * @var int|null
     */
    protected $linePosition;

    /**
     * File in which the violation has been found.
     *
     * @var string
     */
    protected $filename;

    /**
     * Sniff that has produce this violation.
     *
     * @var SniffInterface
     */
    protected $sniff;

    /**
     * @param int    $level
     * @param string $message
     * @param int    $line
     * @param string $filename
     */
    public function __construct($level, $message, $line, $filename)
    {
        $this->level        = $level;
        $this->message      = $message;
        $this->line         = $line;
        $this->filename     = $filename;

        $this->sniff        = null;
        $this->linePosition = null;
    }

    /**
     * Get the level of this violation.
     *
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Get a human-readable of the level of this violation.
     *
     * @return string
     */
    public function getLevelAsString()
    {
        switch ($this->level) {
            case SniffInterface::MESSAGE_TYPE_NOTICE:
                return 'NOTICE';
            case SniffInterface::MESSAGE_TYPE_WARNING:
                return 'WARNING';
            case SniffInterface::MESSAGE_TYPE_ERROR:
                return 'ERROR';
        }

        throw new \Exception(sprintf('Unknown level "%s"', $this->level));
    }

    /**
     * Get the integer value for a given string $level.
     *
     * @param int $level
     *
     * @return int
     */
    public static function getLevelAsInt($level)
    {
        switch (strtoupper($level)) {
            case 'NOTICE':
                return SniffInterface::MESSAGE_TYPE_NOTICE;
            case 'WARNING':
                return SniffInterface::MESSAGE_TYPE_WARNING;
            case 'ERROR':
                return SniffInterface::MESSAGE_TYPE_ERROR;
        }

        throw new \Exception(sprintf('Unknown level "%s"', $level));
    }

    /**
     * Get the text message of this violation.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Get the line number where this violation occured.
     *
     * @return int
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * Get the filename (and path) where this violation occured.
     *
     * @return string
     */
    public function getFilename()
    {
        return (string) $this->filename;
    }

    /**
     * Set the position in the line where this violation occured.
     *
     * @param int $linePosition
     *
     * @return self
     */
    public function setLinePosition($linePosition)
    {
        $this->linePosition = $linePosition;

        return $this;
    }

    /**
     * Get the position in the line, if any.
     *
     * @return int
     */
    public function getLinePosition()
    {
        return $this->linePosition;
    }

    /**
     * Set the sniff that was not met.
     *
     * @param SniffInterface $sniff
     *
     * @return self
     */
    public function setSniff(SniffInterface $sniff)
    {
        $this->sniff = $sniff;

        return $this;
    }

    /**
     * Get the sniff that was not met.
     *
     * @return SniffInterface
     */
    public function getSniff()
    {
        return $this->sniff;
    }
}
