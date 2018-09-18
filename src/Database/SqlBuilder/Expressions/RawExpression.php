<?php

namespace Database\SqlBuilder\Expressions;

class RawExpression extends AbstractExpression
{
    public function __construct(string $expression)
    {
        $this->sql .= $expression;
    }
}