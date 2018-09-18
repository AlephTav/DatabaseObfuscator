<?php

use PHPUnit\Framework\TestCase;
use Database\Connections\ConnectionFactory;
use Database\Connections\Connection;

/**
 * @group integration
 */
class ObfuscatorIntegrationTest extends TestCase
{
    /**
     * The database connection instance.
     *
     * @var Connection
     */
    private static $connection;

    /**
     * The obfuscator instance.
     *
     * @var Obfuscator
     */
    private $obfuscator;

    private static $inputDump = __DIR__ . '/input_dump.sql';

    private static $outputDump = __DIR__ . '/output_dump.sql';

    public static function setUpBeforeClass()
    {
        self::$connection = ConnectionFactory::create([
            'driver' => 'mysql',
            'database' => 'test',
            'username' => 'root',
            'password' => '',
            'host' => '127.0.0.1',
            'port' => 3306,
            'options' => []
        ]);
    }

    public static function tearDownAfterClass()
    {
        self::$connection->disconnect();
        if (file_exists(self::$outputDump)) {
            unlink(self::$outputDump);
        }
    }

    public function setUp()
    {
        $this->obfuscator = new Obfuscator(self::$connection);
        $this->obfuscator->restoreDatabaseFromDump(self::$inputDump);
    }

    public function testObfuscateTableWithoutKey(): void
    {
        $schema = [
            'table_without_key' => [
                'columns' => [
                    'email' => [
                        'type' => 'email',
                        'min' => 10,
                        'max' => 15,
                        'chars' => 'abcde12345'
                    ],
                    'text' => [
                        'type' => 'string',
                        'min' => 0,
                        'max' => 5,
                        'minChar' => 65,
                        'maxChar' => 90
                    ]
                ]
            ]
        ];

        $this->obfuscator->obfuscate($schema);

        $this->checkTableWithoutKey();

        $this->obfuscator->createDumpFromDatabase(self::$outputDump);
        $this->obfuscator->restoreDatabaseFromDump(self::$outputDump);

        $this->checkTableWithoutKey();
    }

    private function checkTableWithoutKey(): void
    {
        $rows = $this->rows('table_without_key', 'name');

        $this->assertCount(3, $rows);
        foreach ($rows as $n => $row) {
            $this->assertRegExp('/^[abcde12345@.]{10,15}$/', $row['email']);
            $this->assertRegExp('/^[A-Z]{0,5}$/', $row['text']);
            $this->assertSame('Name ' . ($n + 1), $row['name']);
        }
    }

    public function testTableWithKey(): void
    {
        $schema = [
            'table_with_key' => [
                'key' => ['id', 'email'],
                'columns' => [
                    'email' => [
                        'type' => 'email',
                        'min' => 6,
                        'max' => 7,
                        'chars' => 'ABCD012'
                    ],
                    'name' => [
                        'type' => 'string',
                        'min' => 0,
                        'max' => 10,
                        'minChar' => 96,
                        'maxChar' => 122
                    ]
                ]
            ]
        ];

        $this->obfuscator->obfuscate($schema);

        $this->checkTableWithKey();

        $this->obfuscator->createDumpFromDatabase(self::$outputDump);
        $this->obfuscator->restoreDatabaseFromDump(self::$outputDump);

        $this->checkTableWithKey();
    }

    private function checkTableWithKey(): void
    {
        $rows = $this->rows('table_with_key', 'text');

        $this->assertCount(3, $rows);
        foreach ($rows as $n => $row) {
            $this->assertRegExp('/^[ABCD012@.]{6,7}$/', $row['email']);
            $this->assertRegExp('/^[`a-z]{0,10}$/', $row['name']);
            $this->assertSame('Text ' . ($n + 1), $row['text']);
        }
    }

    private function rows(string $table, string $orderBy): array
    {
        return self::$connection
            ->query()
            ->from($table)
            ->orderBy($orderBy)
            ->rows();
    }
}