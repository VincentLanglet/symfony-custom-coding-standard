<?php

namespace TwigCS\Tests;

use \Exception;
use \ReflectionClass;
use PHPUnit\Framework\TestCase;
use TwigCS\Environment\StubbedEnvironment;
use TwigCS\Report\Report;
use TwigCS\Ruleset\Ruleset;
use TwigCS\Runner\Fixer;
use TwigCS\Runner\Linter;
use TwigCS\Sniff\SniffInterface;
use TwigCS\Token\Tokenizer;

/**
 * Class AbstractSniffTest
 */
abstract class AbstractSniffTest extends TestCase
{
    /**
     * Should call $this->checkGenericSniff(new Sniff(), [...]);
     */
    abstract public function testSniff();

    /**
     * @param SniffInterface $sniff
     * @param array          $expects
     */
    protected function checkGenericSniff(SniffInterface $sniff, array $expects)
    {
        $env = new StubbedEnvironment();
        $tokenizer = new Tokenizer($env);
        $linter = new Linter($env, $tokenizer);
        $ruleset = new Ruleset();

        try {
            $class = new ReflectionClass(get_called_class());
            $className = $class->getShortName();
            $file = __DIR__.'/Fixtures/'.$className.'.twig';

            $ruleset->addSniff($sniff);
            $report = $linter->run([$file], $ruleset);
        } catch (Exception $e) {
            $this->fail($e->getMessage());

            return;
        }

        $messages = $report->getMessages();
        $messagePositions = [];

        foreach ($messages as $message) {
            if (Report::MESSAGE_TYPE_FATAL === $message->getLevel()) {
                $errorMessage = $message->getMessage();
                $line = $message->getLine();

                if (null !== $line) {
                    $errorMessage = sprintf('Line %s: %s', $line, $errorMessage);
                }
                $this->fail($errorMessage);
            }

            $messagePositions[] = [$message->getLine() => $message->getLinePosition()];
        }
        $this->assertEquals($expects, $messagePositions);

        $fixedFile = __DIR__.'/Fixtures/'.$className.'.fixed.twig';
        if (file_exists($fixedFile)) {
            $fixer = new Fixer($ruleset, $tokenizer);
            $sniff->enableFixer($fixer);
            $fixer->fixFile($file);

            $diff = $fixer->generateDiff($fixedFile);
            if ('' !== $diff) {
                $this->fail($diff);
            }
        }
    }
}
