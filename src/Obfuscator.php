<?php

use Database\Connections\Connection;
use Database\Dump\Dump;
use Data\Generators\DataGeneratorFactory;

/**
 * Database obfuscator.
 */
class Obfuscator
{
    /**
     * The database connection instance.
     *
     * @var Connection
     */
    private $connection;

    /**
     * The database dump utility.
     *
     * @var Dump
     */
    private $dump;

    /**
     * Constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->dump = $connection->dump();
    }

    /**
     * Restores the database from the sql dump.
     *
     * @param string $inputDumpPath
     * @return void
     */
    public function restoreDatabaseFromDump(string $inputDumpPath): void
    {
        $this->dump->restoreDatabaseFromDump($inputDumpPath);
    }

    /**
     * Creates the sql dump of the database.
     *
     * @param string $outputDumpPath
     * @return void
     */
    public function createDumpFromDatabase(string $outputDumpPath): void
    {
        $this->dump->createDumpFromDatabase($outputDumpPath);
    }

    /**
     * Obfuscates the data according to the given obfuscation schema.
     *
     * @param array $schema
     * @return void
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     * @throws RuntimeException
     */
    public function obfuscate(array $schema): void
    {
        foreach ($schema as $table => $data) {
            $keyColumns = (array)($data['key'] ?? []);
            $columns = (array)($data['columns'] ?? []);
            $generators = $this->getColumnValueGenerators($columns);

            if ($keyColumns) {
                $this->obfuscateRowsByKey($table, $keyColumns, $generators);
            } else {
                $this->obfuscateRows($table, $generators);
            }
        }
    }

    /**
     *
     * Returns the list of column value generators.
     *
     * @param array $columns
     * @return array
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     */
    private function getColumnValueGenerators(array $columns): array
    {
        $generators = [];
        foreach ($columns as $column => $options) {
            $generators[$column] = DataGeneratorFactory::create($options);
        }
        return $generators;
    }

    /**
     * Replaces column values for each row by random values.
     *
     * @param string $table
     * @param array $keyColumns
     * @param array $generators
     * @return void
     * @throws RuntimeException
     */
    private function obfuscateRowsByKey(string $table, array $keyColumns, array $generators): void
    {
        /** @var array $keyValues */
        foreach ($this->getKeyValues($table, $keyColumns) as $keyValues) {
            foreach ($keyValues as $key) {
                $this->connection
                    ->update()
                    ->table($table)
                    ->assign($this->getRandomColumnValues($generators))
                    ->where($key)
                    ->exec();
            }
        }
    }

    /**
     * Returns values of the given columns.
     *
     * @param string $table
     * @param array $keyColumns
     * @return Generator
     * @throws RuntimeException
     */
    private function getKeyValues(string $table, array $keyColumns): Generator
    {
        $page = 0;
        $size = 100;
        $query = $this->connection
            ->query()
            ->from($table)
            ->select($keyColumns);
        while ($keyValues = $query->paginate($page, $size)->rows()) {
            yield $keyValues;
            ++$page;
        }
    }

    /**
     * Replaces all given columns in the table by random values.
     *
     * @param string $table
     * @param array $generators
     */
    private function obfuscateRows(string $table, array $generators): void
    {
        $this->connection
            ->update()
            ->table($table)
            ->assign($this->getRandomColumnValues($generators))
            ->exec();
    }

    /**
     * Generates random values for columns.
     *
     * @param array $generators
     * @return array
     */
    private function getRandomColumnValues(array $generators): array
    {
        $values = [];
        foreach ($generators as $column => $generator) {
            $values[$column] = $generator->next();
        }
        return $values;
    }
}