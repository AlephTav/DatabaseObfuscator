<?php

namespace Database\Connections;

use Database\Dump\{Dump, MySqlDump};

/**
 * The MySQL connection representation.
 */
class MySqlConnection extends AbstractConnection
{
    /**
     * The UNIX socket value.
     *
     * @var string
     */
    protected $socket = '';

    /**
     * The database host.
     *
     * @var string
     */
    protected $host = '127.0.0.1';

    /**
     * The database port.
     *
     * @var int
     */
    protected $port = 3306;

    /**
     * The charset used for database connection.
     *
     * @var string
     */
    protected $charset = 'utf8mb4';

    /**
     * The collation used for database connection.
     *
     * @var string
     */
    protected $collation = 'utf8mb4_unicode_ci';

    /**
     * The timezone used for database connection.
     *
     * @var string
     */
    protected $timezone = '';

    /**
     * The SQL modes.
     *
     * @var array
     */
    protected $modes = [];

    /**
     * Returns an instance of the database dump utility.
     *
     * @return MySqlDump
     */
    public function dump(): Dump
    {
        return new MySqlDump($this);
    }

    /**
     * Returns the UNIX socket value.
     *
     * @return string
     */
    public function getSocket(): string
    {
        return $this->socket;
    }

    /**
     * Sets the UNIX socket value.
     *
     * @param string $socket
     * @return void
     */
    public function setSocket(string $socket): void
    {
        $this->socket = $socket;
    }

    /**
     * Returns the database host.
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Sets the database host.
     *
     * @param string $host
     * @return void
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    /**
     * Returns the database port.
     *
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * Sets the database port.
     *
     * @param int $port
     * @return void
     */
    public function setPort(int $port): void
    {
        $this->port = $port;
    }

    /**
     * Returns the database charset.
     *
     * @return string
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     * Sets the database charset.
     *
     * @param string $charset
     * @return void
     */
    public function setCharset(string $charset): void
    {
        $this->charset = $charset;
    }

    /**
     * Returns the database collation.
     *
     * @return string
     */
    public function getCollation(): string
    {
        return $this->collation;
    }

    /**
     * Sets the database collation.
     *
     * @param string $collation
     * @return void
     */
    public function setCollation(string $collation): void
    {
        $this->collation = $collation;
    }

    /**
     * Returns the database timezone.
     *
     * @return string
     */
    public function getTimezone(): string
    {
        return $this->timezone;
    }

    /**
     * Sets the database collation.
     *
     * @param string $timezone
     * @return void
     */
    public function setTimezone(string $timezone): void
    {
        $this->timezone = $timezone;
    }

    /**
     * Returns the database SQL modes.
     *
     * @return array
     */
    public function getModes(): array
    {
        return $this->modes;
    }

    /**
     * Sets the database collation.
     *
     * @param string|array $modes
     * @return void
     */
    public function setModes($modes): void
    {
        $this->modes = \is_array($modes) ? $modes : explode(',', $modes);
    }

    /**
     * Returns the database connection string.
     *
     * @return string
     */
    public function getDsn(): string
    {
        $database = $this->getDatabase();
        if ($this->getSocket() !== '') {
            return $this->getDriver() . ':unix_socket=' . $this->getSocket() . ';dbname=' . $database;
        }
        return $this->getDriver() . ':host=' . $this->getHost() .
            ($this->getPort() ? ';port=' . $this->getPort() : '')  . ';dbname=' . $database;
    }

    /**
     * Initializes the database connection.
     *
     * @return void
     */
    protected function initConnection(): void
    {
        $this->setConnectionCharset();
        $this->setConnectionTimezone();
        $this->setConnectionModes();
    }

    /**
     * Sets the charset for the current connection.
     *
     * @return void
     */
    private function setConnectionCharset(): void
    {
        if ($this->getCharset() !== '') {
            $pdo = $this->getPdo();
            $collation = $this->getCollation();
            $pdo->exec(
                'SET NAMES ' . $pdo->quote($this->getCharset()) .
                ($collation !== '' ? ' COLLATE ' . $pdo->quote($collation) : '')
            );
        }
    }

    /**
     * Sets the timezone for the current connection.
     *
     * @return void
     */
    private function setConnectionTimezone(): void
    {
        if ($this->getTimezone() !== '') {
            $pdo = $this->getPdo();
            $pdo->exec('SET time_zone = ' . $pdo->quote($this->getTimezone()));
        }
    }

    /**
     * Sets the SQL modes for the current connection.
     *
     * @return void
     */
    private function setConnectionModes(): void
    {
        if ($this->getModes()) {
            $pdo = $this->getPdo();
            $modes = implode(',', $this->getModes());
            $pdo->exec('SET SESSION sql_mode = ' . $pdo->quote($modes));
        }
    }

    /**
     * Returns the list of properties that can be set by setConfig()
     * and can be obtained by getConfig() methods.
     *
     * @return array
     */
    protected function getConfigProperties(): array
    {
        return [
            'driver',
            'socket',
            'database',
            'host',
            'port',
            'username',
            'password',
            'options',
            'charset',
            'collation',
            'timezone',
            'modes'
        ];
    }
}