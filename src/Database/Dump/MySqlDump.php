<?php

namespace Database\Dump;

use RuntimeException;
use InvalidArgumentException;
use Database\Connections\MySqlConnection;

/**
 * MySQL dump utility.
 */
class MySqlDump implements Dump
{
    /**
     * The database connection instance.
     *
     * @var MySqlConnection
     */
    private $connection;

    /**
     * Constructor.
     *
     * @param MySqlConnection $connection
     */
    public function __construct(MySqlConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Restores the database from the dump file.
     *
     * @param string $dumpFilePath
     * @return void
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function restoreDatabaseFromDump(string $dumpFilePath): void
    {
        if (!file_exists($dumpFilePath)) {
            throw new InvalidArgumentException('Dump file not found.');
        }

        $cmd = $this->getCommand('<', $dumpFilePath);

        if ($this->executeCommand($cmd) > 0) {
            throw new \RuntimeException('Database restoring failed.');
        }
    }

    /**
     * Creates the database dump file.
     *
     * @param string $dumpFilePath
     * @throws RuntimeException
     */
    public function createDumpFromDatabase(string $dumpFilePath): void
    {
        $cmd = $this->getCommand('>', $dumpFilePath);

        if ($this->executeCommand($cmd) > 0) {
            throw new \RuntimeException('Dump creation failed.');
        }
    }

    /**
     * Returns the shell command that allows to create a database dump or restore a database from it.
     *
     * @param string $direction
     * @param string $dumpPath
     * @return string
     * @throws RuntimeException
     */
    private function getCommand(string $direction, string $dumpPath): string
    {
        $settings = $this->getEscapedConnectionSettings();
        $cmd = [
            $direction === '>' ? 'mysqldump' : 'mysql',
            '-u ' . $settings['username'],
            '-p' . $settings['password']
        ];
        if ($settings['host'] !== '') {
            $cmd[] = '-h ' . $settings['host'];
        }
        if ($settings['port'] !== '') {
            $cmd[] = '-P ' . $settings['port'];
        }
        if ($settings['database'] !== '') {
            $cmd[] = $settings['database'];
        }
        $cmd[] = $direction;
        $cmd[] = $dumpPath;
        return implode(' ', $cmd);
    }

    /**
     * Returns the database connection settings all values of which are shell-escaped.
     *
     * @return array
     * @throws RuntimeException If the database username is not set.
     */
    private function getEscapedConnectionSettings(): array
    {
        $db = $this->connection;
        if ($db->getUsername() === '') {
            throw new RuntimeException('User for login to database is not set.');
        }
        return [
            'username' => escapeshellarg($db->getUsername()),
            'password' => escapeshellarg($db->getPassword()),
            'host' => escapeshellarg($db->getHost()),
            'port' => escapeshellarg($db->getPort()),
            'database' => escapeshellarg($this->connection->getDatabase())
        ];
    }

    /**
     * Executes the command and returns the error code.
     *
     * @param string $command
     * @return int zero on success and non-zero on failure.
     */
    protected function executeCommand(string $command): int
    {
        system($command, $error);
        return $error;
    }
}