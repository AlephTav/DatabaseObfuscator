<?php

namespace Database\Connections;

use Database\Dump\Dump;
use Database\SqlBuilder\UpdateQuery;
use Database\SqlBuilder\Query;

/**
 * The database connection interface.
 */
interface Connection
{
    /**
     * Establishes the database connection.
     *
     * @return void
     */
    public function connect(): void;

    /**
     * Disconnects from the database connection.
     *
     * @return void
     */
    public function disconnect(): void;

    /**
     * Executes the SQL statement.
     *
     * @param string $sql The SQL statement.
     * @param array $params The parameters to be bound to the SQL statement.
     * @return int The number of rows affected by the command execution.
     */
    public function execute(string $sql, array $params): int;

    /**
     * Executes the SQL statement and returns all rows.
     *
     * @param string $sql The SQL statement.
     * @param array $params The parameters to be bound to the SQL statement.
     * @return array
     */
    public function rows(string $sql, array $params): array;

    /**
     * Executes the SQL statement and returns the first row of the result.
     *
     * @param string $sql The SQL statement.
     * @param array $params The parameters to be bound to the SQL statement.
     * @return array
     */
    public function row(string $sql, array $params): array;

    /**
     * Executes the SQL statement and returns the given column of the result.
     *
     * @param string $sql The SQL statement.
     * @param array $params The parameters to be bound to the SQL statement.
     * @return array
     */
    public function column(string $sql, array $params): array;

    /**
     * Executes the SQL statement and returns the first column's value of the first row.
     *
     * @param string $sql The SQL statement.
     * @param array $params The parameters to be bound to the SQL statement.
     * @return mixed
     */
    public function scalar(string $sql, array $params);

    /**
     * Returns an instance of the database dump utility.
     *
     * @return Dump
     */
    public function dump(): Dump;

    /**
     * Returns an instance of the SELECT query.
     *
     * @return Query
     */
    public function query(): Query;

    /**
     * Returns an instance of the UPDATE query.
     *
     * @return UpdateQuery
     */
    public function update(): UpdateQuery;
}