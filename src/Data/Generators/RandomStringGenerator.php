<?php

namespace Data\Generators;

use InvalidArgumentException;

class RandomStringGenerator extends AbstractDataGenerator
{
    /**
     * The minimum length of string.
     *
     * @var int
     */
    protected $min = 1;

    /**
     * The maximum length of string.
     *
     * @var int
     */
    protected $max = 10;

    protected $minChar = 33;

    protected $maxChar = 126;

    /**
     * The character list to be used as alphabet for a random string.
     *
     * @var string
     */
    protected $chars = '';

    public function getMin(): int
    {
        return $this->min;
    }

    public function setMin(int $min): void
    {
        $this->min = $min;
    }

    public function getMax(): int
    {
        return $this->max;
    }

    public function setMax(int $max): void
    {
        $this->max = $max;
    }

    public function getMinChar(): int
    {
        return $this->minChar;
    }

    public function setMinChar(int $minChar): void
    {
        $this->minChar = $minChar;
    }

    public function getMaxChar(): int
    {
        return $this->maxChar;
    }

    public function setMaxChar(int $maxChar): void
    {
        $this->maxChar = $maxChar;
    }

    public function getChars(): string
    {
        return $this->chars;
    }

    public function setChars(string $chars)
    {
        $this->chars = $chars;
    }

    /**
     * Validates the generator properties.
     *
     * @return void
     * @throws InvalidArgumentException
     */
    protected function validate(): void
    {
        if ($this->min < 0) {
            throw new InvalidArgumentException('The minimum length must be positive.');
        }
        if ($this->min > $this->max) {
            throw new InvalidArgumentException('The maximum length must be greater than the minimum one.');
        }
        if ($this->chars === '') {
            if ($this->minChar < 0) {
                throw new InvalidArgumentException('The minimum char code must be positive.');
            }
            if ($this->minChar > $this->maxChar) {
                throw new InvalidArgumentException('The maximum char code must be greater than the minimum one.');
            }
        }
    }

    protected function generateValue()
    {
        if ($this->chars === '') {
            return $this->generateValueFromCharRange();
        }
        return $this->generateValueFromCharList();
    }

    private function generateValueFromCharList(): string
    {
        $str = '';
        $max = \strlen($this->chars) - 1;
        $size = mt_rand($this->min, $this->max);
        for ($i = $size; $i > 0; --$i) {
            $str .= $this->chars[mt_rand(0, $max)];
        }
        return $str;
    }

    private function generateValueFromCharRange(): string
    {
        $str = '';
        $size = mt_rand($this->min, $this->max);
        for ($i = $size; $i > 0; --$i) {
            $str .= \chr(mt_rand($this->minChar, $this->maxChar));
        }
        return $str;
    }
}