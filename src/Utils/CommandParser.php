<?php

namespace Utils;

use InvalidArgumentException;

/**
 * Simple parser of the command line argument.
 */
class CommandParser
{
    /**
     * Path to the initial database dump.
     *
     * @var string
     */
    private $inputDumpPath;

    /**
     * Path to the obfuscated database dump.
     *
     * @var string
     */
    private $outputDumpPath;

    /**
     * Path to the obfuscator configuration.
     *
     * @var string
     */
    private $configPath;

    /**
     * The base directory of the obfuscator.
     *
     * @var string
     */
    private $baseDirectory;

    /**
     * Constructor.
     *
     * @param string $baseDirectory
     * @param array $args
     * @throws InvalidArgumentException
     */
    public function __construct(string $baseDirectory, array $args)
    {
        $this->baseDirectory = $baseDirectory;
        $this->parseArguments($args);
    }

    /**
     * Returns the path to the input dump file.
     *
     * @return string
     */
    public function getInputDumpPath(): string
    {
        return $this->inputDumpPath;
    }

    /**
     * Returns the path to the output dump file.
     *
     * @return string
     */
    public function getOutputDumpPath(): string
    {
        return $this->outputDumpPath;
    }

    /**
     * Returns the path to the configuration file.
     *
     * @return string
     */
    public function getConfigPath(): string
    {
        return $this->configPath;
    }

    /**
     * Parses and normalizes the given command arguments.
     *
     * @param array $args
     * @return void
     * @throws InvalidArgumentException
     */
    private function parseArguments(array $args): void
    {
        if (\count($args) < 3) {
            throw new InvalidArgumentException('Too few arguments.');
        }
        $this->configPath = $this->normalizePath(array_pop($args));
        $this->outputDumpPath = $this->normalizePath(array_pop($args));
        $this->inputDumpPath = $this->normalizePath(array_pop($args));
    }

    /**
     * Normalizes the give path.
     *
     * @param string $path
     * @return string
     */
    private function normalizePath(string $path): string
    {
        return $this->baseDirectory . DIRECTORY_SEPARATOR . ltrim($path, '\/');
    }
}