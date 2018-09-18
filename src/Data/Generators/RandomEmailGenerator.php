<?php

namespace Data\Generators;

use InvalidArgumentException;

/**
 * Generator of random emails.
 */
class RandomEmailGenerator extends RandomStringGenerator
{
    /**
     * The minimum length of generated emails.
     */
    public const MIN_EMAIL_LENGTH = 6;

    /**
     * The character list to be used as alphabet for a random string.
     *
     * @var string
     */
    protected $chars = '0123456789abcdefghijklmnopqrstuvwxyz';

    /**
     * Generates a random email.
     *
     * @return string
     */
    protected function generateValue(): string
    {
        $email = parent::generateValue();
        $max = \strlen($email) - 1;
        $email = substr_replace($email, '@', mt_rand(1, max($max - 5, 1)), 1);
        $email = substr_replace($email, '.', mt_rand(max($max - 3, 3), $max - 2), 1);
        return $email;
    }

    /**
     * Validates the generator properties.
     *
     * @return void
     * @throws InvalidArgumentException
     */
    protected function validate(): void
    {
        parent::validate();
        if ($this->min < static::MIN_EMAIL_LENGTH) {
            throw new InvalidArgumentException('The email address must not be shorter than 6 characters.');
        }
    }
}