<?php

use PHPUnit\Framework\TestCase;
use Database\SqlBuilder\Expressions\AbstractExpression;

abstract class AbstractQueryTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function setUp()
    {
        $property = (new ReflectionClass(AbstractExpression::class))->getProperty('parameterIndex');
        $property->setAccessible(true);
        $property->setValue(0);
    }
}