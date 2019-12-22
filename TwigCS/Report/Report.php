<?php

namespace TwigCS\Report;

/**
 * Report contains all violations with stats.
 */
class Report
{
    const MESSAGE_TYPE_NOTICE  = 0;
    const MESSAGE_TYPE_WARNING = 1;
    const MESSAGE_TYPE_ERROR   = 2;
    const MESSAGE_TYPE_FATAL   = 3;

    /**
     * @var SniffViolation[]
     */
    protected $messages;

    /**
     * @var string[]
     */
    protected $files;

    /**
     * @var int
     */
    protected $totalNotices;

    /**
     * @var int
     */
    protected $totalWarnings;

    /**
     * @var int
     */
    protected $totalErrors;

    public function __construct()
    {
        $this->messages = [];
        $this->files = [];
        $this->totalNotices = 0;
        $this->totalWarnings = 0;
        $this->totalErrors = 0;
    }

    /**
     * @param SniffViolation $sniffViolation
     *
     * @return $this
     */
    public function addMessage(SniffViolation $sniffViolation): Report
    {
        // Update stats
        switch ($sniffViolation->getLevel()) {
            case self::MESSAGE_TYPE_NOTICE:
                ++$this->totalNotices;
                break;
            case self::MESSAGE_TYPE_WARNING:
                ++$this->totalWarnings;
                break;
            case self::MESSAGE_TYPE_ERROR:
            case self::MESSAGE_TYPE_FATAL:
                ++$this->totalErrors;
                break;
        }

        $this->messages[] = $sniffViolation;

        return $this;
    }

    /**
     * @param array $filters
     *
     * @return SniffViolation[]
     */
    public function getMessages(array $filters = []): array
    {
        if (0 === count($filters)) {
            // Return all messages, without filtering.
            return $this->messages;
        }

        return array_filter($this->messages, function (SniffViolation $message) use ($filters) {
            $fileFilter = true;
            $levelFilter = true;

            if (isset($filters['file']) && $filters['file']) {
                $fileFilter = (string) $message->getFilename() === (string) $filters['file'];
            }

            if (isset($filters['level']) && $filters['level']) {
                $levelFilter = $message->getLevel() >= $message::getLevelAsInt($filters['level']);
            }

            return $fileFilter && $levelFilter;
        });
    }

    /**
     * @param string $file
     */
    public function addFile(string $file): void
    {
        $this->files[] = $file;
    }

    /**
     * @return string[]
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @return int
     */
    public function getTotalFiles(): int
    {
        return count($this->files);
    }

    /**
     * @return int
     */
    public function getTotalMessages(): int
    {
        return count($this->messages);
    }

    /**
     * @return int
     */
    public function getTotalNotices(): int
    {
        return $this->totalNotices;
    }

    /**
     * @return int
     */
    public function getTotalWarnings(): int
    {
        return $this->totalWarnings;
    }

    /**
     * @return int
     */
    public function getTotalErrors(): int
    {
        return $this->totalErrors;
    }
}
