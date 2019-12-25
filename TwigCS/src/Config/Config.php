<?php

declare(strict_types=1);

namespace TwigCS\Config;

use Exception;
use Symfony\Component\Finder\Finder;

/**
 * TwigCS configuration data.
 */
class Config
{
    /**
     * @var array
     */
    public static $defaultConfig = [
        'exclude'          => [],
        'pattern'          => '*.twig',
        'paths'            => [],
        'workingDirectory' => '',
    ];

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this::$defaultConfig, $config);
    }

    /**
     * @return Finder
     *
     * @throws Exception
     */
    public function findFiles(): Finder
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
