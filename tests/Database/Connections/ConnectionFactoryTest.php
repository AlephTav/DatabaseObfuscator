<?php

use PHPUnit\Framework\TestCase;
use Database\Connections\{ConnectionFactory, MySqlConnection};

class ConnectionFactoryTest extends TestCase
{
    public function testEmptyDriver(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ConnectionFactory::create([]);
    }

    public function testUnsupportedDriver(): void
    {
        $this->expectException(UnexpectedValueException::class);
        ConnectionFactory::create(['driver' => 'oci']);
    }

    public function testMySqlConnection(): void
    {
        /** @var MySqlConnection $connection */
        $connection = ConnectionFactory::create([
            'driver' => 'mysql',
            'database' => 'test',
            'username' => 'root',
            'password' => '123',
            'host' => '127.0.0.1',
            'port' => 3306,
            'options' => [
                PDO::ATTR_EMULATE_PREPARES => true
            ]
        ]);

        $this->assertInstanceOf(MySqlConnection::class, $connection);
        $this->assertEquals([
            'driver' => 'mysql',
            'database' => 'test',
            'username' => 'root',
            'password' => '123',
            'host' => '127.0.0.1',
            'port' => 3306,
            'socket' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'timezone' => '',
            'modes' => [],
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_STRINGIFY_FETCHES => false,
                PDO::ATTR_EMULATE_PREPARES => true
            ]
        ], $connection->getConfig());
    }
}