<?php

declare(strict_types=1);

namespace TwigCS\Config;

use Exception;

/**
 * TwigCS configuration data.
 */
class Config
{
    /**
     * @var array
     */
    protected $paths = [];

    /**
     * @param array $paths
     */
    public function __construct(array $paths = [])
    {
        $this->paths = $paths;
    }

    /**
     * @return array
     *
     * @throws Exception
     */
    public function findFiles(): array
    {
        $files = [];
        foreach ($this->paths as $path) {
            if (is_dir($path)) {
                $flags = \RecursiveDirectoryIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS;
                $directoryIterator = new \RecursiveDirectoryIterator($path, $flags);
            } else {
                $directoryIterator = new \RecursiveArrayIterator([new \SplFileInfo($path)]);
            }

            $filter = new TwigFileFilter($directoryIterator);
            $iterator = new \RecursiveIteratorIterator($filter);

            /** @var \SplFileInfo $file */
            foreach ($iterator as $file) {
                $files[] = $file->getRealPath();
            }
        }

        return $files;
    }
}
