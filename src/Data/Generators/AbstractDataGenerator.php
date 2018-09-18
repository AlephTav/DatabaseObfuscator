<?php

namespace Data\Generators;

/**
 * Base class for all data generators.
 */
abstract class AbstractDataGenerator implements DataGenerator
{
    /**
     * Constructor.
     *
     * @param array $properties
     */
    public function __construct(array $properties = [])
    {
        if ($properties) {
            $this->setProperties($properties);
        }
    }

    /**
     * Returns the next generated value.
     *
     * @return mixed
     */
    public function next()
    {
        $this->validate();
        return $this->generateValue();
    }

    /**
     * Validates the generator properties.
     */
    protected function validate(): void {}

    /**
     * Generates a value.
     *
     * @return mixed
     */
    abstract protected function generateValue();

    /**
     * Sets the generator properties.
     *
     * @param array $properties
     */
    private function setProperties(array $properties): void
    {
        foreach ($properties as $property => $value) {
            $setter = 'set' . ucfirst($property);
            if (method_exists($this, $setter)) {
                $this->{$setter}($value);
            }
        }
    }
}