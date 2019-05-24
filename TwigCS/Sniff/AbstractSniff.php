<?php

namespace TwigCS\Sniff;

use \Exception;
use TwigCS\Report\Report;

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
     * @return string
     */
    abstract public function getType();
}
