<?php

declare(strict_types=1);

namespace TwigCS\Runner;

use Exception;
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
     * @param iterable $files
     * @param Ruleset  $ruleset
     * @param bool     $fix
     *
     * @return Report
     *
     * @throws Exception
     */
    public function run(iterable $files, Ruleset $ruleset, bool $fix = false): Report
    {
        $report = new Report();

        if ($fix) {
            $this->fix($files, $ruleset);
        }

        foreach ($ruleset->getSniffs() as $sniff) {
            $sniff->enableReport($report);
        }

        // Process
        foreach ($files as $file) {
            $file = strval($file);
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
     * @param iterable $files
     * @param Ruleset  $ruleset
     *
     * @throws Exception
     */
    public function fix(iterable $files, Ruleset $ruleset): void
    {
        $fixer = new Fixer($ruleset, $this->tokenizer);

        foreach ($ruleset->getSniffs() as $sniff) {
            $sniff->enableFixer($fixer);
        }

        foreach ($files as $file) {
            $file = strval($file);
            $success = $fixer->fixFile($file);

            if (!$success) {
                throw new Exception("Cannot fix the file $file.");
            }

            file_put_contents($file, $fixer->getContents());
        }
    }

    /**
     * @param string  $file
     * @param Ruleset $ruleset
     * @param Report  $report
     *
     * @return bool
     */
    public function processTemplate(string $file, Ruleset $ruleset, Report $report): bool
    {
        $twigSource = new Source(file_get_contents($file), $file);

        // Tokenize + Parse.
        try {
            $this->env->parse($this->env->tokenize($twigSource));
        } catch (Error $e) {
            $sniffViolation = new SniffViolation(
                Report::MESSAGE_TYPE_FATAL,
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
                Report::MESSAGE_TYPE_FATAL,
                sprintf('Unable to tokenize file: %s', $e->getMessage()),
                $file
            );

            $report->addMessage($sniffViolation);

            return false;
        }

        /** @var SniffInterface[] $sniffs */
        $sniffs = $ruleset->getSniffs();
        foreach ($sniffs as $sniff) {
            $sniff->processFile($stream);
        }

        return true;
    }

    /**
     * @param Report      $report
     * @param string|null $file
     */
    protected function setErrorHandler(Report $report, string $file = null): void
    {
        set_error_handler(function ($type, $message) use ($report, $file) {
            if (E_USER_DEPRECATED === $type) {
                $sniffViolation = new SniffViolation(
                    Report::MESSAGE_TYPE_NOTICE,
                    $message,
                    $file
                );

                $report->addMessage($sniffViolation);
            }
        });
    }
}
