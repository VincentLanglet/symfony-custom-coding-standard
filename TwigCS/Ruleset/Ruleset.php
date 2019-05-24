<?php

namespace TwigCS\Ruleset;

use \Exception;
use TwigCS\Sniff\PostParserSniffInterface;
use TwigCS\Sniff\PreParserSniffInterface;
use TwigCS\Sniff\SniffInterface;

/**
 * Set of rules to be used by TwigCS and contains all sniffs (pre or post).
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
     * @param array|null $types
     *
     * @return SniffInterface[]
     */
    public function getSniffs($types = null)
    {
        if (null === $types) {
            $types = [SniffInterface::TYPE_PRE_PARSER, SniffInterface::TYPE_POST_PARSER];
        }

        if (null !== $types && !is_array($types)) {
            $types = [$types];
        }

        return array_filter($this->sniffs, function (SniffInterface $sniff) use ($types) {
            return in_array($sniff->getType(), $types);
        });
    }

    /**
     * @param PreParserSniffInterface $sniff
     *
     * @return $this
     */
    public function addPreParserSniff(PreParserSniffInterface $sniff)
    {
        $this->sniffs[get_class($sniff)] = $sniff;

        return $this;
    }

    /**
     * @param PostParserSniffInterface $sniff
     *
     * @return $this
     */
    public function addPostParserSniff(PostParserSniffInterface $sniff)
    {
        $this->sniffs[get_class($sniff)] = $sniff;

        return $this;
    }

    /**
     * @param SniffInterface $sniff
     *
     * @return $this
     *
     * @throws Exception
     */
    public function addSniff(SniffInterface $sniff)
    {
        if (SniffInterface::TYPE_PRE_PARSER === $sniff->getType()) {
            // Store this type of sniff locally.
            /** @var PreParserSniffInterface $sniff */
            $this->addPreParserSniff($sniff);

            return $this;
        }

        if (SniffInterface::TYPE_POST_PARSER === $sniff->getType()) {
            // Store this type of sniff locally.
            /** @var PostParserSniffInterface $sniff */
            $this->addPostParserSniff($sniff);

            return $this;
        }

        throw new Exception(sprintf(
            'Unknown type of sniff "%s", expected one of: "%s"',
            $sniff->getType(),
            implode(', ', [SniffInterface::TYPE_PRE_PARSER, SniffInterface::TYPE_POST_PARSER])
        ));
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
