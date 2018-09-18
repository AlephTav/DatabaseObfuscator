<?php

namespace Database\Connections;

use PDO;
use PDOStatement;
use Database\SqlBuilder\UpdateQuery;
use Database\SqlBuilder\Query;

/**
 * The base class to represent a connection to a database via PDO.
 */
abstract class AbstractConnection implements Connection
{
    /**
     * The database driver.
     *
     * @var string
     */
    protected $driver = '';

    /**
     * The database name or path to the database file.
     *
     * @var string
     */
    protected $database = '';

    /**
     * The user name for establishing DB connection.
     * Defaults to an empty string meaning no username to use.
     *
     * @var string
     */
    protected $username = '';

    /**
     * The password for establishing DB connection.
     * Defaults to an empty string meaning no password to use.
     *
     * @var string
     */
    protected $password = '';

    /**
     * The PDO connection options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * The default PDO connection options.
     *
     * @var array
     */
    protected $defaultOptions = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES => false
    ];

    /**
     * The PDO instance.
     *
     * @var PDO
     */
    private $pdo;

    /**
     * Constructor.
     *
     * @param array $config The connection settings.
     */
    public function __construct(array $config = [])
    {
        if ($config) {
            $this->setConfig($config);
        }
    }

    /**
     * Returns the database driver.
     *
     * @return string
     */
    public function getDriver(): string
    {
        return $this->driver;
    }

    /**
     * Sets the database driver (DSN prefix).
     *
     * @param string $driver
     * @return void
     */
    public function setDriver(string $driver): void
    {
        $this->driver = strtolower($driver);
    }

    /**
     * Returns the database name.
     *
     * @return string
     */
    public function getDatabase(): string
    {
        return $this->database;
    }

    /**
     * Sets the database name.
     *
     * @param string $name
     * @return void
     */
    public function setDatabase(string $name): void
    {
        $this->database = $name;
    }

    /**
     * Returns the username for establishing DB connection.
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Sets the username for establishing DB connection.
     *
     * @param string $username
     * @return void
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * Returns the password for establishing DB connection.
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Sets the password for establishing DB connection.
     *
     * @param string $password
     * @return void
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * Returns the default PDO connection options.
     *
     * @return array
     */
    public function getDefaultOptions(): array
    {
        return $this->defaultOptions;
    }

    /**
     * Returns the PDO options.
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Sets the PDO options.
     *
     * @param array $options
     * @return void
     */
    public function setOptions(array $options): void
    {
        $this->options = array_diff_key($this->defaultOptions, $options) + $options;
    }

    /**
     * Returns the parameters of the database connection.
     *
     * @return array
     */
    public function getConfig(): array
    {
        $config = [];
        foreach ($this->getConfigProperties() as $property) {
            $config[$property] = $this->{$property};
        }
        return $config;
    }

    /**
     * Sets the parameters of the database connection.
     *
     * @param array $config
     * @return void
     */
    public function setConfig(array $config): void
    {
        foreach ($this->getConfigProperties() as $property) {
            if (isset($config[$property])) {
                $this->{'set' . ucfirst($property)}($config[$property]);
            }
        }
    }

    /**
     * Returns the list of properties that can be set by setConfig()
     * and can be obtained by getConfig() methods.
     *
     * @return array
     */
    abstract protected function getConfigProperties(): array;

    /**
     * Executes the SQL statement.
     *
     * @param string $sql The SQL statement.
     * @param array $params The parameters to be bound to the SQL statement.
     * @return int The number of rows affected by the command execution.
     */
    public function execute(string $sql, array $params = []): int
    {
        $st = $this->executeInternal($sql, $params);
        return $st->rowCount();
    }

    /**
     * Executes the SQL statement.
     *
     * @param string $sql The SQL statement.
     * @param array $params The parameters to be bound to the SQL statement.
     * @return PDOStatement
     */
    private function executeInternal(string $sql, array $params = []): PDOStatement
    {
        $st = $this->getPdo()->prepare($sql);
        $st->execute($params);
        return $st;
    }

    /**
     * Executes the SQL statement and returns all rows.
     *
     * @param string $sql The SQL statement.
     * @param array $params The parameters to be bound to the SQL statement.
     * @return array
     */
    public function rows(string $sql, array $params = []): array
    {
        $st = $this->executeInternal($sql, $params);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Executes the SQL statement and returns the first row of the result.
     *
     * @param string $sql The SQL statement.
     * @param array $params The parameters to be bound to the SQL statement.
     * @return array
     */
    public function row(string $sql, array $params = []): array
    {
        $st = $this->executeInternal($sql, $params);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row === false ? [] : $row;
    }

    /**
     * Executes the SQL statement and returns the given column of the result.
     *
     * @param string $sql The SQL statement.
     * @param array $params The parameters to be bound to the SQL statement.
     * @return array
     */
    public function column(string $sql, array $params): array
    {
        $st = $this->executeInternal($sql, $params);
        return $st->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_COLUMN, 0);
    }

    /**
     * Executes the SQL statement and returns the first column's value of the first row.
     *
     * @param string $sql The SQL statement.
     * @param array $params The parameters to be bound to the SQL statement.
     * @return mixed
     */
    public function scalar(string $sql, array $params)
    {
        $st = $this->executeInternal($sql, $params);
        return $st->fetchColumn(0);
    }

    /**
     * Returns an instance of the SELECT query.
     *
     * @return Query
     */
    public function query(): Query
    {
        return new Query($this);
    }

    /**
     * Returns an instance of the UPDATE query.
     *
     * @return UpdateQuery
     */
    public function update(): UpdateQuery
    {
        return new UpdateQuery($this);
    }

    /**
     * Establishes the database connection and returns a PDO instance.
     *
     * @return PDO
     */
    public function getPdo(): PDO
    {
        $this->connect();
        return $this->pdo;
    }

    /**
     * Returns TRUE if the current connection is active, FALSE otherwise.
     *
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->pdo !== null;
    }

    /**
     * Establishes the database connection.
     *
     * @return void
     */
    public function connect(): void
    {
        if (!$this->isConnected()) {
            $this->pdo = $this->createPdo();
            $this->initConnection();
        }
    }

    /**
     * Disconnects from the database connection.
     *
     * @return void
     */
    public function disconnect(): void
    {
        $this->pdo = null;
    }

    /**
     * Create a new PDO instance.
     *
     * @return PDO
     */
    protected function createPdo(): PDO
    {
        return new PDO($this->getDsn(), $this->getUsername(), $this->getPassword(), $this->getOptions());
    }

    /**
     * Returns the database connection string.
     *
     * @return string
     */
    abstract protected function getDsn(): string;

    /**
     * Initializes the database connection.
     *
     * @return void
     */
    protected function initConnection(): void {}
}