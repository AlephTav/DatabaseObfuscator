<?php

use PHPUnit\Framework\TestCase;
use Utils\Config;

class ConfigTest extends TestCase
{
    public function testNonExistingConfig(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Config(__DIR__ . '/foo.php');
    }

    public function testGetConfig(): void
    {
        $file = __DIR__ . '/config.php';
        $config = new Config($file);
        $this->assertSame(require($file), $config->get());
    }

    public function testGetVariables(): void
    {
        $config = new Config(__DIR__ . '/config.php');

        $this->assertEquals(1, $config->get('var1'));
        $this->assertEquals('test', $config->get('var4.4.var2'));
        $this->assertEquals('test', $config->get(['var4', '4', 'var2']));
        $this->assertEquals(['var1' => 'abc'], $config->get('section2'));
        $this->assertEquals('none', $config->get('var5', 'none'));
        $this->assertEquals('none', $config->get('var5.1.2.3.4', 'none'));
        $this->assertEquals('none', $config->get('var5|1|2|3|4', 'none', '|'));
        $this->assertEquals('test', $config->get('var4#4#var2', null, '#'));
    }
}