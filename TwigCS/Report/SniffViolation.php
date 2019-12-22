<?php

namespace TwigCS\Report;

use LogicException;
use TwigCS\Sniff\SniffInterface;

/**
 * Wrapper class that represents a violation to a sniff with context.
 */
class SniffViolation
{
    const LEVEL_NOTICE  = 'NOTICE';
    const LEVEL_WARNING = 'WARNING';
    const LEVEL_ERROR   = 'ERROR';
    const LEVEL_FATAL   = 'FATAL';

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
     * @var int|null
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
     * @param int      $level
     * @param string   $message
     * @param string   $filename
     * @param int|null $line
     */
    public function __construct(int $level, string $message, string $filename, int $line = null)
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
            case Report::MESSAGE_TYPE_NOTICE:
                return self::LEVEL_NOTICE;
            case Report::MESSAGE_TYPE_WARNING:
                return self::LEVEL_WARNING;
            case Report::MESSAGE_TYPE_ERROR:
                return self::LEVEL_ERROR;
            case Report::MESSAGE_TYPE_FATAL:
                return self::LEVEL_FATAL;
            default:
                throw new LogicException();
        }
    }

    /**
     * Get the integer value for a given string $level.
     *
     * @param string $level
     *
     * @return int
     */
    public static function getLevelAsInt(string $level)
    {
        switch (strtoupper($level)) {
            case self::LEVEL_NOTICE:
                return Report::MESSAGE_TYPE_NOTICE;
            case self::LEVEL_WARNING:
                return Report::MESSAGE_TYPE_WARNING;
            case self::LEVEL_ERROR:
                return Report::MESSAGE_TYPE_ERROR;
            case self::LEVEL_FATAL:
                return Report::MESSAGE_TYPE_FATAL;
            default:
                throw new LogicException();
        }
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
     * @return int|null
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
        return $this->filename;
    }

    /**
     * Set the position in the line where this violation occured.
     *
     * @param int $linePosition
     *
     * @return self
     */
    public function setLinePosition(int $linePosition)
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
