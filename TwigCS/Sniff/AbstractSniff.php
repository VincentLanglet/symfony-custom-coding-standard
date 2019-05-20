<?php

namespace TwigCS\Sniff;

use TwigCS\Report\Report;

/**
 * Base for all sniff.
 */
abstract class AbstractSniff implements SniffInterface
{
    /**
     * Default options for all sniffs.
     *
     * @var array
     */
    protected static $defaultOptions = [];

    /**
     * Computed options of this sniffs.
     *
     * @var array
     */
    protected $options;

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

    /**
     * @param array $options Each sniff can defined its options.
     */
    public function __construct($options = [])
    {
        $this->messages = [];
        $this->report   = null;
        $this->options  = array_merge(self::$defaultOptions, $options);

        $this->configure();
    }

    /**
     * Configure this sniff based on its options.
     *
     * @return void
     */
    public function configure()
    {
        // Nothing.
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
     */
    public function getReport()
    {
        if (null === $this->report) {
            throw new \Exception('Sniff is disabled!');
        }

        return $this->report;
    }

    /**
     * @return string
     */
    abstract public function getType();
}
