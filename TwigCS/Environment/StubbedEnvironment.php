<?php

namespace TwigCS\Environment;

use Twig\Environment;
use Twig\Loader\LoaderInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;
use TwigCS\Extension\SniffsExtension;
use TwigCS\Token\TokenParser;

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

    /**
     * @var \Closure
     */
    private $stubCallable;

    /**
     * @param LoaderInterface|null $loader
     * @param array                $options
     */
    public function __construct(LoaderInterface $loader = null, $options = [])
    {
        parent::__construct($loader, $options);

        $this->stubCallable  = function () {
            /* This will be used as stub filter, function or test */
        };

        $this->stubFilters   = [];
        $this->stubFunctions = [];

        if (isset($options['stub_tags'])) {
            foreach ($options['stub_tags'] as $tag) {
                $this->addTokenParser(new TokenParser($tag));
            }
        }

        $this->stubTests = [];
        if (isset($options['stub_tests'])) {
            foreach ($options['stub_tests'] as $test) {
                $this->stubTests[$test] = new TwigTest('stub', $this->stubCallable);
            }
        }

        $this->addExtension(new SniffsExtension());
    }

    /**
     * @param string $name
     *
     * @return TwigFilter
     */
    public function getFilter($name)
    {
        if (!isset($this->stubFilters[$name])) {
            $this->stubFilters[$name] = new TwigFilter('stub', $this->stubCallable);
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
            $this->stubFunctions[$name] = new TwigFunction('stub', $this->stubCallable);
        }

        return $this->stubFunctions[$name];
    }

    /**
     * @param string $name
     *
     * @return false|TwigTest
     */
    public function getTest($name)
    {
        $test = parent::getTest($name);
        if ($test) {
            return $test;
        }

        if (isset($this->stubTests[$name])) {
            return $this->stubTests[$name];
        }

        return false;
    }
}
