<?php

namespace TwigCS;

use \Exception;
use \Traversable;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Source;
use TwigCS\Report\Report;
use TwigCS\Report\SniffViolation;
use TwigCS\Ruleset\Ruleset;
use TwigCS\Sniff\SniffInterface;
use TwigCS\Token\Tokenizer;

/**
 * Linter is the main class and will process twig files against a set of rules.
 */
class Linter
{
    /**
     * @var Environment
     */
    protected $env;

    /**
     * @var Tokenizer
     */
    protected $tokenizer;

    /**
     * @param Environment $env
     * @param Tokenizer   $tokenizer
     */
    public function __construct(Environment $env, Tokenizer $tokenizer)
    {
        $this->env = $env;

        $this->tokenizer = $tokenizer;
    }

    /**
     * Run the linter on the given $files against the given $ruleset.
     *
     * @param array|string $files   List of files to process.
     * @param Ruleset      $ruleset Set of rules to check.
     *
     * @return Report an object with all violations and stats.
     *
     * @throws Exception
     */
    public function run($files, Ruleset $ruleset)
    {
        if (!is_array($files) && !$files instanceof Traversable) {
            $files = [$files];
        }

        if (empty($files)) {
            throw new Exception('No files to process, provide at least one file to be linted');
        }

        $report = new Report();
        foreach ($ruleset->getSniffs() as $sniff) {
            $sniff->enable($report);
        }

        // Process
        foreach ($files as $file) {
            $this->setErrorHandler($report, $file);
            $this->processTemplate($file, $ruleset, $report);

            // Add this file to the report.
            $report->addFile($file);
        }
        restore_error_handler();

        // tearDown
        foreach ($ruleset->getSniffs() as $sniff) {
            $sniff->disable();
        }

        return $report;
    }

    /**
     * Checks one template against the set of rules.
     *
     * @param string  $file    File to check as a string.
     * @param Ruleset $ruleset Set of rules to check.
     * @param Report  $report  Current report to fill.
     *
     * @return bool
     */
    public function processTemplate($file, $ruleset, $report)
    {
        $twigSource = new Source(file_get_contents($file), $file, $file);

        // Tokenize + Parse.
        try {
            $this->env->parse($this->env->tokenize($twigSource));
        } catch (Error $e) {
            $sniffViolation = new SniffViolation(
                SniffInterface::MESSAGE_TYPE_ERROR,
                $e->getRawMessage(),
                $e->getSourceContext()->getName(),
                $e->getTemplateLine()
            );

            $report->addMessage($sniffViolation);

            return false;
        }

        // Tokenizer.
        try {
            $stream = $this->tokenizer->tokenize($twigSource);
        } catch (Exception $e) {
            $sniffViolation = new SniffViolation(
                SniffInterface::MESSAGE_TYPE_ERROR,
                sprintf('Unable to tokenize file'),
                (string) $file
            );

            $report->addMessage($sniffViolation);

            return false;
        }

        /** @var SniffInterface[] $sniffs */
        $sniffs = $ruleset->getSniffs();
        foreach ($sniffs as $sniff) {
            foreach ($stream as $index => $token) {
                $sniff->process($token, $index, $stream);
            }
        }

        return true;
    }

    /**
     * @param Report      $report
     * @param string|null $file
     */
    protected function setErrorHandler(Report $report, $file = null)
    {
        set_error_handler(function ($type, $message) use ($report, $file) {
            if (E_USER_DEPRECATED === $type) {
                $sniffViolation = new SniffViolation(
                    SniffInterface::MESSAGE_TYPE_NOTICE,
                    $message,
                    $file
                );

                $report->addMessage($sniffViolation);
            }
        });
    }
}
