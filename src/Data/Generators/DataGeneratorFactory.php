<?php

namespace Data\Generators;

use InvalidArgumentException;
use UnexpectedValueException;

/**
 * The data generator factory.
 */
class DataGeneratorFactory
{
    /**
     * Creates a data generator of the given type.
     *
     * @param array $properties The generator properties.
     * @return DataGenerator
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     */
    public static function create(array $properties): DataGenerator
    {
        $type = $properties['type'] ?? '';
        switch (strtolower($type)) {
            case 'string':
                return new RandomStringGenerator($properties);
            case 'email':
                return new RandomEmailGenerator($properties);
            case '':
                throw new InvalidArgumentException('The data type is not specified.');
            default:
                throw new UnexpectedValueException("The data type $type is not supported.");
        }
    }
}