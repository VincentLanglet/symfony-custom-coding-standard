<?php

namespace TwigCS\Environment;

use Symfony\Bridge\Twig\TokenParser\DumpTokenParser;
use Symfony\Bridge\Twig\TokenParser\FormThemeTokenParser;
use Symfony\Bridge\Twig\TokenParser\StopwatchTokenParser;
use Symfony\Bridge\Twig\TokenParser\TransChoiceTokenParser;
use Symfony\Bridge\Twig\TokenParser\TransDefaultDomainTokenParser;
use Symfony\Bridge\Twig\TokenParser\TransTokenParser;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

/**
 * Provide stubs for all filters, functions, tests and tags that are not defined in twig's core.
 */
class StubbedEnvironment extends Environment
{
    /**
     * @var TwigFilter[]
     */
    private $stubFilters;

    /**
     * @var TwigFunction[]
     */
    private $stubFunctions;

    /**
     * @var TwigTest[]
     */
    private $stubTests;

    public function __construct()
    {
        parent::__construct(new ArrayLoader());

        $this->addTokenParser(new DumpTokenParser());
        $this->addTokenParser(new FormThemeTokenParser());
        $this->addTokenParser(new StopwatchTokenParser(false));
        $this->addTokenParser(new TransChoiceTokenParser());
        $this->addTokenParser(new TransDefaultDomainTokenParser());
        $this->addTokenParser(new TransTokenParser());

        $this->stubFilters = [];
        $this->stubFunctions = [];
        $this->stubTests = [];
    }

    /**
     * @param string $name
     *
     * @return TwigFilter
     */
    public function getFilter($name)
    {
        if (!isset($this->stubFilters[$name])) {
            $this->stubFilters[$name] = new TwigFilter('stub');
        }

        return $this->stubFilters[$name];
    }

    /**
     * @param string $name
     *
     * @return TwigFunction
     */
    public function getFunction($name)
    {
        if (!isset($this->stubFunctions[$name])) {
            $this->stubFunctions[$name] = new TwigFunction('stub');
        }

        return $this->stubFunctions[$name];
    }

    /**
     * @param string $name
     *
     * @return TwigTest
     */
    public function getTest($name)
    {
        if (!isset($this->stubTests[$name])) {
            $this->stubTests[$name] = new TwigTest('stub');
        }

        return $this->stubTests[$name];
    }
}
