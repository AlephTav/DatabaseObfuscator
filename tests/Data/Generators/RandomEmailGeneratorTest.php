<?php

use PHPUnit\Framework\TestCase;
use Data\Generators\RandomEmailGenerator;

class RandomEmailGeneratorTest extends TestCase
{
    public function testInvalidMinLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new RandomEmailGenerator(['min' => RandomEmailGenerator::MIN_EMAIL_LENGTH - 1]))->next();
    }

    public function testInvalidMaxLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new RandomEmailGenerator(['min' => 7, 'max' => 2]))->next();
    }

    public function testInvalidMinChar()
    {
        $this->expectException(InvalidArgumentException::class);
        (new RandomEmailGenerator(['minChar' => -5]))->next();
    }

    public function testInvalidMaxChar()
    {
        $this->expectException(InvalidArgumentException::class);
        (new RandomEmailGenerator(['minchar' => 10, 'maxChar' => 5]))->next();
    }

    /**
     * @dataProvider configProvider
     */
    public function testValueGeneration(array $properties, string $regex): void
    {
        $n = 11;
        $generator = new RandomEmailGenerator($properties);
        while (--$n) {
            $this->assertRegExp($regex, $generator->next());
        }
    }

    public function configProvider(): array
    {
        return [
            [
                [
                    'min' => 6,
                    'max' => 10,
                    'chars' => '1234567890abcdef'
                ],
                '/^[0-9a-f@.]{5,10}$/'
            ],
            [
                [
                    'min' => 10,
                    'max' => 20,
                    'minChar' => 97,
                    'maxChar' => 122,
                    'chars' => ''
                ],
                '/^[a-z@.]{10,20}$/'
            ]
        ];
    }
}