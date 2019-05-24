<?php

namespace TwigCS\Config;

use \Exception;
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
        'stub'             => [],
        'workingDirectory' => '',
    ];

    /**
     * Current configuration.
     *
     * @var array
     */
    protected $config;

    public function __construct()
    {
        $args = func_get_args();

        $this->config = $this::$defaultConfig;
        foreach ($args as $arg) {
            $this->config = array_merge($this->config, $arg);
        }
    }

    /**
     * Find all files to process, based on a file or directory and exclude patterns.
     *
     * @return array
     *
     * @throws Exception
     */
    public function findFiles()
    {
        $paths = $this->get('paths');
        $exclude = $this->get('exclude');

        // Build the finder.
        $files = Finder::create()
            ->in($this->get('workingDirectory'))
            ->name($this->config['pattern'])
            ->files();

        // Include all matching paths.
        foreach ($paths as $path) {
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
    public function get($key)
    {
        if (!isset($this->config[$key])) {
            throw new Exception(sprintf('Configuration key "%s" does not exist', $key));
        }

        return $this->config[$key];
    }
}
