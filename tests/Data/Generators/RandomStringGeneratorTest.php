<?php

use PHPUnit\Framework\TestCase;
use Data\Generators\RandomStringGenerator;

class RandomStringGeneratorTest extends TestCase
{
    public function testInvalidMinLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new RandomStringGenerator(['min' => -3]))->next();
    }

    public function testInvalidMaxLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new RandomStringGenerator(['min' => 5, 'max' => 2]))->next();
    }

    public function testInvalidMinChar()
    {
        $this->expectException(InvalidArgumentException::class);
        (new RandomStringGenerator(['minChar' => -5]))->next();
    }

    public function testInvalidMaxChar()
    {
        $this->expectException(InvalidArgumentException::class);
        (new RandomStringGenerator(['minchar' => 10, 'maxChar' => 5]))->next();
    }

    /**
     * @dataProvider configProvider
     */
    public function testValueGeneration(array $properties, string $regex): void
    {
        $n = 11;
        $generator = new RandomStringGenerator($properties);
        while (--$n) {
            $this->assertRegExp($regex, $generator->next());
        }
    }

    public function configProvider(): array
    {
        return [
            [
                [
                    'min' => 5,
                    'max' => 10,
                    'chars' => '1234567890abcdef'
                ],
                '/^[0-9a-f]{5,10}$/'
            ],
            [
                [
                    'min' => 0,
                    'max' => 5,
                    'chars' => 'xyz012#@'
                ],
                '/^[012xyz#@]{0,7}$/'
            ],
            [
                [
                    'min' => 10,
                    'max' => 20,
                    'minChar' => 64,
                    'maxChar' => 90
                ],
                '/^[@A-Z]{10,20}$/'
            ]
        ];
    }
}