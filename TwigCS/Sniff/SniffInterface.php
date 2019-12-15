<?php

namespace TwigCS\Sniff;

use TwigCS\Report\Report;
use TwigCS\Runner\Fixer;
use TwigCS\Token\Token;

/**
 * Interface for all sniffs.
 */
interface SniffInterface
{
    /**
     * Enable the sniff report.
     *
     * Once the sniff is enabled, it will be registered and executed when a template is tokenized or parsed.
     * Messages will be added to the given `$report` object.
     *
     * @param Report $report
     *
     * @return self
     */
    public function enableReport(Report $report);

    /**
     * Enable the sniff fixer.
     *
     * @param Fixer $fixer
     *
     * @return self
     */
    public function enableFixer(Fixer $fixer);

    /**
     * Disable the sniff.
     *
     * It usually is disabled when the processing is over, it will reset the sniff internal values for next check.
     *
     * @return self
     */
    public function disable();

    /**
     * @param Token[] $stream
     */
    public function processFile(array $stream);
}
