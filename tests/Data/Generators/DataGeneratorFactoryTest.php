<?php

use PHPUnit\Framework\TestCase;
use Data\Generators\{DataGeneratorFactory, RandomStringGenerator, RandomEmailGenerator};

class DataGeneratorFactoryTest extends TestCase
{
    public function testEmptyProperties(): void
    {
        $this->expectException(InvalidArgumentException::class);
        DataGeneratorFactory::create([]);
    }

    public function testNotSupportedType(): void
    {
        $this->expectException(UnexpectedValueException::class);
        DataGeneratorFactory::create(['type' => 'ip']);
    }

    public function testRandomString(): void
    {
        /** @var RandomStringGenerator $generator */
        $generator = DataGeneratorFactory::create([
            'type' => 'string',
            'min' => 3,
            'max' => 7,
            'minChar' => 100,
            'maxChar' => 120,
            'chars' => 'abc123'
        ]);

        $this->assertInstanceOf(RandomStringGenerator::class, $generator);
        $this->assertSame(3, $generator->getMin());
        $this->assertSame(7, $generator->getMax());
        $this->assertSame(100, $generator->getMinChar());
        $this->assertSame(120, $generator->getMaxChar());
        $this->assertSame('abc123', $generator->getChars());
    }

    public function testRandomEmail(): void
    {
        /** @var RandomEmailGenerator $generator */
        $generator = DataGeneratorFactory::create([
            'type' => 'email',
            'min' => 11,
            'max' => 15,
            'minChar' => 13,
            'maxChar' => 212,
            'chars' => 'aAbBcC1230'
        ]);

        $this->assertInstanceOf(RandomEmailGenerator::class, $generator);
        $this->assertSame(11, $generator->getMin());
        $this->assertSame(15, $generator->getMax());
        $this->assertSame(13, $generator->getMinChar());
        $this->assertSame(212, $generator->getMaxChar());
        $this->assertSame('aAbBcC1230', $generator->getChars());
    }
}