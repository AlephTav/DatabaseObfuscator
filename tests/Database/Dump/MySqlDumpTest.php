<?php

use PHPUnit\Framework\TestCase;
use Database\Connections\MySqlConnection;
use Database\Dump\MySqlDump;


class MySqlDumpTest extends TestCase
{
    /**
     * The executed shell command.
     *
     * @var string
     */
    private $command;

    private static $dump = __DIR__ . '/../../input_dump.sql';

    public function testRestoreDatabaseFailedDumpNotFound(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new MySqlDump(new MySqlConnection()))
            ->restoreDatabaseFromDump('foo.sql');

    }

    public function testRestoreDatabaseFailedNoUser(): void
    {
        $this->expectException(RuntimeException::class);

        /** @var MySqlDump $dump */
        $dump = $this->getDumpWithStub();
        $dump->restoreDatabaseFromDump(self::$dump);
    }

    public function testRestoreDatabaseFailedInvalidCommand(): void
    {
        $this->expectException(RuntimeException::class);

        /** @var MySqlDump $dump */
        $dump = $this->getDumpWithStub(['username' => 'root'], true);
        $dump->restoreDatabaseFromDump(self::$dump);
    }

    public function testRestoreDatabaseSuccess(): void
    {
        /** @var MySqlDump $dump */
        $dump = $this->getDumpWithStub([
            'database' => 'test',
            'username' => 'user',
            'password' => '123',
            'host' => '10.0.0.1',
            'port' => 3300
        ]);
        $dump->restoreDatabaseFromDump(self::$dump);

        $this->assertSame(
            'mysql -u "user" -p"123" -h "10.0.0.1" -P "3300" "test" < ' . self::$dump,
            $this->command
        );
    }

    public function testCreateDumpFailedNoUser(): void
    {
        $this->expectException(RuntimeException::class);

        /** @var MySqlDump $dump */
        $dump = $this->getDumpWithStub();
        $dump->createDumpFromDatabase(self::$dump);
    }

    public function testCreateDumpFailedInvalidCommand(): void
    {
        $this->expectException(RuntimeException::class);

        /** @var MySqlDump $dump */
        $dump = $this->getDumpWithStub(['username' => 'root'], true);
        $dump->createDumpFromDatabase(self::$dump);
    }

    public function testCreateDumpSuccess(): void
    {
        /** @var MySqlDump $dump */
        $dump = $this->getDumpWithStub([
            'database' => 'test',
            'username' => 'user',
            'password' => '123',
            'host' => '10.0.0.1',
            'port' => 3300
        ]);
        $dump->createDumpFromDatabase(self::$dump);

        $this->assertSame(
            'mysqldump -u "user" -p"123" -h "10.0.0.1" -P "3300" "test" > ' . self::$dump,
            $this->command
        );
    }

    private function getDumpWithStub(array $config = [], bool $commandExecutionFailed = false)
    {
        $this->command = '';

        $mock = $this->getMockBuilder(MySqlDump::class)
            ->setConstructorArgs([new MySqlConnection($config)])
            ->setMethods(['executeCommand'])
            ->getMock();

        $mock->method('executeCommand')
            ->willReturnCallback(function($command) use($commandExecutionFailed) {
                $this->command = $command;
                return (int)$commandExecutionFailed;
            });

        return $mock;
    }
}