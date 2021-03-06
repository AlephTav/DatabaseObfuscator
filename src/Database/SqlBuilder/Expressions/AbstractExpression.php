<?php

namespace Database\SqlBuilder\Expressions;

/**
 * The base class for all SQL expressions.
 */
class AbstractExpression
{
    /**
     * The SQL statement.
     *
     * @var string
     */
    protected $sql = '';

    /**
     * The SQL statement parameters.
     *
     * @var array
     */
    protected $params = [];

    /**
     * Used to form a parameter name.
     *
     * @var int
     */
    private static $parameterIndex = 0;

    //region Factory Methods

    public static function raw(string $expression): RawExpression
    {
        return new RawExpression($expression);
    }

    public static function condition(): ConditionalExpression
    {
        return new ConditionalExpression();
    }

    //endregion

    public function toSql(): string
    {
        return $this->sql;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function addParams(array $params): void
    {
        $this->params = array_merge($this->params, $params);
    }

    /**
     * Generates the next parameter name of a query.
     *
     * @return string
     */
    protected static function nextParameterName(): string
    {
        return 'p' . (++self::$parameterIndex);
    }
}