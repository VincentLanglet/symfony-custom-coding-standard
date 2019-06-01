<?php

namespace TwigCS\Ruleset;

use TwigCS\Sniff\SniffInterface;

/**
 * Set of rules to be used by TwigCS and contains all sniffs.
 */
class Ruleset
{
    /**
     * @var SniffInterface[]
     */
    protected $sniffs;

    public function __construct()
    {
        $this->sniffs = [];
    }

    /**
     * @return SniffInterface[]
     */
    public function getSniffs()
    {
        return $this->sniffs;
    }

    /**
     * @param SniffInterface $sniff
     *
     * @return $this
     */
    public function addSniff(SniffInterface $sniff)
    {
        $this->sniffs[get_class($sniff)] = $sniff;

        return $this;
    }

    /**
     * @param string $sniffClass
     *
     * @return $this
     */
    public function removeSniff($sniffClass)
    {
        if (isset($this->sniffs[$sniffClass])) {
            unset($this->sniffs[$sniffClass]);
        }

        return $this;
    }
}
