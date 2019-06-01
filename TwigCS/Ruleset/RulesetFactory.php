<?php

namespace TwigCS\Ruleset;

use \SplFileInfo;
use Symfony\Component\Finder\Finder;

/**
 * Factory to help create set of rules.
 */
class RulesetFactory
{
    /**
     * Create a new set of rule.
     *
     * @return Ruleset
     */
    public function createStandardRuleset()
    {
        $ruleset = new Ruleset();

        $finder = Finder::create()->in(__DIR__.'/../Sniff/Standard')->files();

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $class = 'TwigCS\Sniff\Standard\\'.explode('.', $file->getFilename())[0];
            $ruleset->addSniff(new $class());
        }

        return $ruleset;
    }
}
