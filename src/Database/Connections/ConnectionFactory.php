<?php

namespace Database\Connections;

use InvalidArgumentException;
use UnexpectedValueException;

/**
 * Use this class to create a database connection instance
 * according to the given configuration.
 */
class ConnectionFactory
{
    /**
     * Creates a database connection based on the configuration.
     *
     * @param array $config
     * @return Connection
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     */
    public static function create(array $config): Connection
    {
        $driver = $config['driver'] ?? '';
        switch (strtolower($driver)) {
            case 'mysql':
            case 'mysqli':
                return new MySqlConnection($config);
            case '':
                throw new InvalidArgumentException('The database driver is not specified.');
            default:
                throw new UnexpectedValueException("The database driver $driver is not supported.");
        }
    }
}