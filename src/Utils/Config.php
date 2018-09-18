<?php

namespace Utils;

use InvalidArgumentException;

/**
 * Simple config representation.
 */
class Config
{
    /**
     * The config settings.
     *
     * @var array
     */
    private $settings = [];

    /**
     * Constructor.
     *
     * @param string $configPath
     * @throws InvalidArgumentException
     */
    public function __construct(string $configPath)
    {
        $this->loadData($configPath);
    }

    /**
     * Returns a value of the config parameter by its name.
     *
     * @param string|array $option
     * @param mixed $default
     * @param string $delimiter
     * @return mixed
     */
    public function get($option = '', $default = null, string $delimiter = '.')
    {
        if (\is_array($option)) {
            $keys = $option;
        } else if ($option === '') {
            $keys = [];
        } else {
            $keys = explode($delimiter, $option);
        }
        $settings = $this->settings;
        foreach ($keys as $key) {
            if (!\is_array($settings) || !array_key_exists($key, $settings)) {
                return $default;
            }
            $settings = $settings[$key];
        }
        return $settings;
    }

    /**
     * Loads the configuration settings.
     *
     * @param string $configPath
     * @throws InvalidArgumentException
     */
    private function loadData(string $configPath): void
    {
        if (!file_exists($configPath)) {
            throw new InvalidArgumentException('Invalid configuration path.');
        }
        $this->settings = require($configPath);
    }
}