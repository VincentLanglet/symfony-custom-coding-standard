<?php

namespace TwigCS\Tests;

use \Exception;
use \ReflectionClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twig\Loader\LoaderInterface;
use TwigCS\Environment\StubbedEnvironment;
use TwigCS\Linter;
use TwigCS\Report\SniffViolation;
use TwigCS\Ruleset\Ruleset;
use TwigCS\Sniff\SniffInterface;
use TwigCS\Token\Tokenizer;

/**
 * Class AbstractSniffTest
 */
abstract class AbstractSniffTest extends TestCase
{
    /**
     * @var StubbedEnvironment
     */
    private $env;

    /**
     * @var Linter
     */
    private $lint;

    public function setUp()
    {
        /** @var LoaderInterface|MockObject $twigLoaderInterface */
        $twigLoaderInterface = $this->getMockBuilder(LoaderInterface::class)->getMock();
        $this->env = new StubbedEnvironment(
            $twigLoaderInterface,
            [
                'stub_tags'  => ['render', 'some_other_block', 'stylesheets'],
                'stub_tests' => ['some_test'],
            ]
        );
        $this->lint = new Linter($this->env, new Tokenizer($this->env));
    }

    /**
     * @param SniffInterface $sniff
     * @param array          $expects
     */
    protected function checkGenericSniff(SniffInterface $sniff, array $expects)
    {
        $ruleset = new Ruleset();
        try {
            $class = new ReflectionClass(get_called_class());
            $file = __DIR__.'/Fixtures/'.$class->getShortName().'.twig';

            $ruleset->addSniff($sniff);
            $report = $this->lint->run($file, $ruleset);
        } catch (Exception $e) {
            $this->fail($e->getMessage());

            return;
        }

        $this->assertEquals(count($expects), $report->getTotalWarnings() + $report->getTotalErrors());
        if ($expects) {
            $messagePositions = array_map(function (SniffViolation $message) {
                return [
                    $message->getLine(),
                    $message->getLinePosition(),
                ];
            }, $report->getMessages());

            $this->assertEquals($expects, $messagePositions);
        }
    }
}
