<?php

namespace Data\Generators;

interface DataGenerator
{
    /**
     * Returns the next generated value.
     *
     * @return mixed
     */
    public function next();
}