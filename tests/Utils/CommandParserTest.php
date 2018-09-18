<?php

use PHPUnit\Framework\TestCase;
use Utils\CommandParser;

class CommandParserTest extends TestCase
{
    public function testFewArguments(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CommandParser(__DIR__, ['a', 'b']);
    }

    public function testPathNormalization(): void
    {
        $base = __DIR__ . DIRECTORY_SEPARATOR;
        $input = 'input_dump.sql';
        $output = 'output_dump.sql';
        $config = 'config.php';

        $cmd = new CommandParser(__DIR__, [$input, $output, $config]);

        $this->assertSame($base . $input, $cmd->getInputDumpPath());
        $this->assertSame($base . $output, $cmd->getOutputDumpPath());
        $this->assertSame($base . $config, $cmd->getConfigPath());
    }
}