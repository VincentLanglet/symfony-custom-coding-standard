<?php

namespace TwigCS\Config;

/**
 * Class TwigFileFilter
 */
class TwigFileFilter extends \RecursiveFilterIterator
{
    /**
     * @param \RecursiveIterator $iterator
     */
    public function __construct(\RecursiveIterator $iterator)
    {
        parent::__construct($iterator);
    }

    /**
     * @return bool
     */
    public function accept()
    {
        /** @var \SplFileInfo $file */
        $file = $this->current();

        return $file->isDir() || 'twig' === $file->getExtension();
    }
}
