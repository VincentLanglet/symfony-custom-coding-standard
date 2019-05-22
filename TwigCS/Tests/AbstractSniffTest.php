<?php

namespace TwigCS\Tests;

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
                'stub_tags'  => ['dump', 'render', 'some_other_block', 'stylesheets', 'trans'],
                'stub_tests' => ['some_test'],
            ]
        );
        $this->lint = new Linter($this->env, new Tokenizer($this->env));
    }

    /**
     * @param string         $filename
     * @param SniffInterface $sniff
     * @param array          $expects
     *
     * @throws \Exception
     */
    protected function checkGenericSniff($filename, SniffInterface $sniff, array $expects)
    {
        $file = __DIR__.'/Fixtures/'.$filename;

        $ruleset = new Ruleset();
        $ruleset->addSniff($sniff);

        $report = $this->lint->run($file, $ruleset);

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
