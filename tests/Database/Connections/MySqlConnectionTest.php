<?php

use PHPUnit\Framework\TestCase;
use Database\Connections\MySqlConnection;
use Database\Dump\MySqlDump;
use Database\SqlBuilder\{Query, UpdateQuery};

class MySqlConnectionTest extends TestCase
{
    /**
     * All performed SQL statements.
     *
     * @var array
     */
    private $statements;

    public function testConnectionConfig(): void
    {
        $config = [
            'driver' => 'mysqli',
            'database' => 'test',
            'username' => 'root',
            'password' => '123',
            'host' => '127.0.0.1',
            'port' => 3306,
            'socket' => '/socket',
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'timezone' => 'UTC',
            'modes' => [
                'HIGH_NOT_PRECEDENCE'
            ],
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_STRINGIFY_FETCHES => true,
                PDO::ATTR_EMULATE_PREPARES => true
            ]
        ];

        $connection = new MySqlConnection($config);

        $this->assertEquals($connection->getConfig(), $config);
        $this->assertSame('mysqli', $connection->getDriver());
        $this->assertSame('test', $connection->getDatabase());
        $this->assertSame('root', $connection->getUsername());
        $this->assertSame('123', $connection->getPassword());
        $this->assertSame('127.0.0.1', $connection->getHost());
        $this->assertSame(3306, $connection->getPort());
        $this->assertSame('/socket', $connection->getSocket());
        $this->assertSame('utf8', $connection->getCharset());
        $this->assertSame('utf8_general_ci', $connection->getCollation());
        $this->assertSame('UTC', $connection->getTimezone());
        $this->assertSame($config['modes'], $connection->getModes());
        $this->assertSame($config['options'], $connection->getOptions());
    }

    public function testGetDsn(): void
    {
        $connection = new MySqlConnection([
            'driver' => 'mysql',
            'database' => 'test',
            'host' => '222.333.444.555',
            'port' => 3333,
            'socket' => '/path/to/socket'
        ]);

        $this->assertSame('mysql:unix_socket=/path/to/socket;dbname=test', $connection->getDsn());
        $connection->setSocket('');
        $this->assertSame('mysql:host=222.333.444.555;port=3333;dbname=test', $connection->getDsn());
    }

    public function testGetDefaultOptions()
    {
        $connection = new MySqlConnection([
            'options' => [
                PDO::ATTR_STRINGIFY_FETCHES => true
            ]
        ]);

        $this->assertEquals([
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            PDO::ATTR_EMULATE_PREPARES => false
        ], $connection->getDefaultOptions());
    }

    public function testGetDump(): void
    {
        $connection = new MySqlConnection();
        $this->assertInstanceOf(MySqlDump::class, $connection->dump());
    }

    public function testGetQuery(): void
    {
        $connection = new MySqlConnection();
        $this->assertInstanceOf(Query::class, $connection->query());
    }

    public function testGetUpdateQuery(): void
    {
        $connection = new MySqlConnection();
        $this->assertInstanceOf(UpdateQuery::class, $connection->update());
    }

    public function testConnection(): void
    {
        $config = [
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'timezone' => 'UTC',
            'modes' => 'NO_ENGINE_SUBSTITUTION'
        ];

        /** @var MySqlConnection $connection */
        $connection = $this->getConnectionWithPdoStub($config);
        $connection->connect();

        $this->assertTrue($connection->isConnected());
        $this->assertEquals([
            ['SET NAMES utf8 COLLATE utf8_general_ci', []],
            ['SET time_zone = UTC', []],
            ['SET SESSION sql_mode = NO_ENGINE_SUBSTITUTION', []]
        ], $this->statements);
    }

    public function testDisconnect(): void
    {
        /** @var MySqlConnection $connection */
        $connection = $this->getConnectionWithPdoStub();
        $connection->connect();

        $this->assertTrue($connection->isConnected());
        $connection->disconnect();
        $this->assertFalse($connection->isConnected());
    }

    public function testExecute(): void
    {
        /** @var MySqlConnection $connection */
        $connection = $this->getConnectionWithPdoStub(['charset' => '', 'collation' => '']);
        $connection->connect();

        $affectedRows = $connection->execute('SOME SQL', ['a', 'b', 'c']);

        $this->assertEquals(1, $affectedRows);
        $this->assertEquals([
            ['SOME SQL', ['a', 'b', 'c']]
        ], $this->statements);
    }

    public function testRows(): void
    {
        /** @var MySqlConnection $connection */
        $connection = $this->getConnectionWithPdoStub(['charset' => '', 'collation' => '']);
        $connection->connect();

        $rows = $connection->rows('SOME SQL', ['a', 'b', 'c']);

        $this->assertEquals([1, 2, 3], $rows);
        $this->assertEquals([
            ['SOME SQL', ['a', 'b', 'c']]
        ], $this->statements);
    }

    public function testRow(): void
    {
        /** @var MySqlConnection $connection */
        $connection = $this->getConnectionWithPdoStub(['charset' => '', 'collation' => '']);
        $connection->connect();

        $row = $connection->row('SOME SQL', ['a', 'b', 'c']);

        $this->assertEquals([1, 2, 3], $row);
        $this->assertEquals([
            ['SOME SQL', ['a', 'b', 'c']]
        ], $this->statements);
    }

    public function testColumn(): void
    {
        /** @var MySqlConnection $connection */
        $connection = $this->getConnectionWithPdoStub(['charset' => '', 'collation' => '']);
        $connection->connect();

        $rows = $connection->column('SOME SQL', ['a', 'b', 'c']);

        $this->assertEquals([1, 2, 3], $rows);
        $this->assertEquals([
            ['SOME SQL', ['a', 'b', 'c']]
        ], $this->statements);
    }

    public function testScalar(): void
    {
        /** @var MySqlConnection $connection */
        $connection = $this->getConnectionWithPdoStub(['charset' => '', 'collation' => '']);
        $connection->connect();

        $result = $connection->scalar('SOME SQL', ['a', 'b', 'c']);

        $this->assertEquals(1, $result);
        $this->assertEquals([
            ['SOME SQL', ['a', 'b', 'c']]
        ], $this->statements);
    }

    private function getConnectionWithPdoStub(array $config = [])
    {
        $this->statements = [];

        $mock = $this->getMockBuilder(MySqlConnection::class)
            ->setConstructorArgs([$config])
            ->setMethods(['createPdo'])
            ->getMock();

        $mock->method('createPdo')
            ->willReturn($this->getMockPdo());

        return $mock;
    }

    private function getMockPdo()
    {
        $exec = function($statement) {
            $this->statements[] = [$statement, []];
        };

        $prepare = function($statement) {
            $this->statements[] = [$statement, []];
            return $this->getMockStatement();
        };

        $mock = $this->getMockBuilder('PDO')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('quote')
            ->willReturnArgument(0);
        $mock->method('exec')
            ->willReturnCallback($exec);
        $mock->method('prepare')
            ->willReturnCallback($prepare);

        return $mock;
    }

    private function getMockStatement()
    {
        $execute = function($params = []) {
            $st = array_pop($this->statements);
            $st[1] = $params;
            $this->statements[] = $st;
            return true;
        };

        $mock = $this->getMockBuilder('PDOStatement')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('execute')
            ->willReturnCallback($execute);
        $mock->method('rowCount')
            ->willReturn(1);
        $mock->method('fetch')
            ->willReturn([1, 2, 3]);
        $mock->method('fetchAll')
            ->willReturn([1, 2, 3]);
        $mock->method('fetchColumn')
            ->willReturn(1);

        return $mock;
    }
}