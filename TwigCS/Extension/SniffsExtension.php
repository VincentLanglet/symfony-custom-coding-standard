<?php

namespace TwigCS\Extension;

use Twig\Extension\AbstractExtension;
use Twig\NodeVisitor\NodeVisitorInterface;
use TwigCS\Sniff\PostParserSniffInterface;

/**
 * This extension is responsible of loading the sniffs into the twig environment.
 *
 * This class is only a bridge between the linter and the `SniffsNodeVisitor` that is
 * actually doing the work when Twig parser is compiling a template.
 */
class SniffsExtension extends AbstractExtension
{
    /**
     * The actual node visitor.
     *
     * @var SniffsNodeVisitor
     */
    protected $nodeVisitor;

    public function __construct()
    {
        $this->nodeVisitor = new SniffsNodeVisitor();
    }

    /**
     * @return NodeVisitorInterface[]
     */
    public function getNodeVisitors()
    {
        return [$this->nodeVisitor];
    }

    /**
     * Register a sniff in the node visitor.
     *
     * @param PostParserSniffInterface $sniff
     *
     * @return self
     */
    public function addSniff(PostParserSniffInterface $sniff)
    {
        $this->nodeVisitor->addSniff($sniff);

        return $this;
    }

    /**
     * Remove a sniff from the node visitor.
     *
     * @param PostParserSniffInterface $sniff
     *
     * @return self
     */
    public function removeSniff(PostParserSniffInterface $sniff)
    {
        $this->nodeVisitor->removeSniff($sniff);

        return $this;
    }
}
