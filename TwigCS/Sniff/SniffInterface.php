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
     * Once the sniff is enabled, it will be registered and executed when a template is tokenized or parsed.
     * Messages will be added to the given `$report` object.
     *
     * @param Report $report
     */
    public function enableReport(Report $report): void;

    /**
     * @param Fixer $fixer
     */
    public function enableFixer(Fixer $fixer): void;

    /**
     * It usually is disabled when the processing is over, it will reset the sniff internal values for next check.
     */
    public function disable(): void;

    /**
     * @param Token[] $stream
     */
    public function processFile(array $stream): void;
}
