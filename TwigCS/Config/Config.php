<?php

namespace TwigCS\Config;

use Exception;
use Symfony\Component\Finder\Finder;

/**
 * TwigCS configuration data.
 */
class Config
{
    /**
     * Default configuration.
     *
     * @var array
     */
    public static $defaultConfig = [
        'exclude'          => [],
        'pattern'          => '*.twig',
        'paths'            => [],
        'workingDirectory' => '',
    ];

    /**
     * Current configuration.
     *
     * @var array
     */
    protected $config;

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this::$defaultConfig, $config);
    }

    /**
     * Find all files to process, based on a file or directory and exclude patterns.
     *
     * @return iterable
     *
     * @throws Exception
     */
    public function findFiles()
    {
        $paths = $this->get('paths');
        $exclude = $this->get('exclude');
        $workingDir = $this->get('workingDirectory');

        // Build the finder.
        $files = Finder::create()
            ->in($workingDir)
            ->name($this->config['pattern'])
            ->files();

        // Include all matching paths.
        foreach ($paths as $path) {
            // Trim absolute path
            if (substr($path, 0, strlen($workingDir)) === $workingDir) {
                $path = ltrim(substr($path, strlen($workingDir)), '/');
            }

            $files->path($path);
        }

        // Exclude all matching paths.
        if ($exclude) {
            $files->exclude($exclude);
        }

        return $files;
    }

    /**
     * Get a configuration value for the given $key.
     *
     * @param string $key
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function get(string $key)
    {
        if (!isset($this->config[$key])) {
            throw new Exception(sprintf('Configuration key "%s" does not exist', $key));
        }

        return $this->config[$key];
    }
}
