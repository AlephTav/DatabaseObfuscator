<?php

namespace Database\Dump;

/**
 * The interface for database dump utility.
 */
interface Dump
{
    /**
     * Restores the database from the dump file.
     *
     * @param string $dumpFilePath
     * @return void
     */
    public function restoreDatabaseFromDump(string $dumpFilePath): void;

    /**
     * Creates the database dump file.
     *
     * @param string $dumpFilePath
     */
    public function createDumpFromDatabase(string $dumpFilePath): void;
}