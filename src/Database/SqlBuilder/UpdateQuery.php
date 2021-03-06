<?php

namespace Database\SqlBuilder;

use RuntimeException;
use Database\Connections\Connection;
use Database\SqlBuilder\Expressions\AbstractExpression;
use Database\SqlBuilder\Expressions\FromExpression;
use Database\SqlBuilder\Expressions\JoinExpression;
use Database\SqlBuilder\Expressions\AssignmentExpression;
use Database\SqlBuilder\Expressions\OrderExpression;
use Database\SqlBuilder\Expressions\WhereExpression;

class UpdateQuery extends AbstractExpression
{
    /**
     * The FROM expression instance.
     *
     * @var FromExpression
     */
    private $from;

    /**
     * The JOIN expression instance.
     *
     * @var JoinExpression
     */
    private $join;

    /**
     * The SET expression instance.
     *
     * @var AssignmentExpression
     */
    private $assignment;

    /**
     * The WHERE expression instance.
     *
     * @var WhereExpression
     */
    private $where;

    /**
     * The ORDER BY expression instance.
     *
     * @var OrderExpression
     */
    private $order;

    /**
     * @var int
     */
    private $limit;

    /**
     * The database connection instance.
     */
    private $connection;

    /**
     * Contains TRUE if the query has built.
     *
     * @var bool
     */
    private $built = false;

    public function __construct(
        Connection $connection = null,
        FromExpression $from = null,
        JoinExpression $join = null,
        AssignmentExpression $assignment = null,
        WhereExpression $where = null,
        OrderExpression $order = null,
        ?int $limit = null
    )
    {
        $this->connection = $connection;
        $this->from = $from;
        $this->where = $where;
        $this->join = $join;
        $this->assignment = $assignment;
        $this->order = $order;
        $this->limit = $limit;
    }

    //region FROM

    public function table($table, $alias = null): UpdateQuery
    {
        $this->from = $this->from ?? new FromExpression();
        $this->from->append($table, $alias);
        $this->built = false;
        return $this;
    }

    //endregion

    //region JOIN

    public function join($table, $conditions = null): UpdateQuery
    {
        return $this->typeJoin('JOIN', $table, $conditions);
    }

    public function innerJoin($table, $conditions = null): UpdateQuery
    {
        return $this->typeJoin('INNER JOIN', $table, $conditions);
    }

    public function crossJoin($table, $conditions = null): UpdateQuery
    {
        return $this->typeJoin('CROSS JOIN', $table, $conditions);
    }

    public function leftJoin($table, $conditions = null): UpdateQuery
    {
        return $this->typeJoin('LEFT JOIN', $table, $conditions);
    }

    public function rightJoin($table, $conditions = null): UpdateQuery
    {
        return $this->typeJoin('RIGHT JOIN', $table, $conditions);
    }

    public function leftOuterJoin($table, $conditions = null): UpdateQuery
    {
        return $this->typeJoin('LEFT OUTER JOIN', $table, $conditions);
    }

    public function rightOuterJoin($table, $conditions = null): UpdateQuery
    {
        return $this->typeJoin('RIGHT OUTER JOIN', $table, $conditions);
    }

    public function naturalLeftJoin($table, $conditions = null): UpdateQuery
    {
        return $this->typeJoin('NATURAL LEFT JOIN', $table, $conditions);
    }

    public function naturalRightJoin($table, $conditions = null): UpdateQuery
    {
        return $this->typeJoin('NATURAL RIGHT JOIN', $table, $conditions);
    }

    public function naturalLeftOuterJoin($table, $conditions = null): UpdateQuery
    {
        return $this->typeJoin('NATURAL LEFT OUTER JOIN', $table, $conditions);
    }

    public function naturalRightOuterJoin($table, $conditions = null): UpdateQuery
    {
        return $this->typeJoin('NATURAL RIGHT OUTER JOIN', $table, $conditions);
    }

    public function straightJoin($table, $conditions = null): UpdateQuery
    {
        return $this->typeJoin('STRAIGHT_JOIN', $table, $conditions);
    }

    private function typeJoin(string $type, $table, $conditions = null): UpdateQuery
    {
        $this->join = $this->join ?? new JoinExpression();
        $this->join->append($type, $table, $conditions);
        $this->built = false;
        return $this;
    }

    //endregion

    //region SET (Assignment List)

    public function assign($column, $value = null): UpdateQuery
    {
        $this->assignment = $this->assignment ?? new AssignmentExpression();
        $this->assignment->append($column, $value);
        $this->built = false;
        return $this;
    }

    //endregion

    //region WHERE

    public function andWhere($column, $operator = null, $value = null): UpdateQuery
    {
        return $this->where($column, $operator, $value, 'AND');
    }

    public function orWhere($column, $operator = null, $value = null): UpdateQuery
    {
        return $this->where($column, $operator, $value, 'OR');
    }

    public function where($column, $operator = null, $value = null, string $connector = 'AND'): UpdateQuery
    {
        $this->where = $this->where ?? new WhereExpression();
        $this->where->with($column, $operator, $value, $connector);
        $this->built = false;
        return $this;
    }

    //endregion

    //region ORDER BY

    public function orderBy($column, $order): UpdateQuery
    {
        $this->order = $this->order ?? new OrderExpression();
        $this->order->append($column, $order);
        $this->built = false;
        return $this;
    }

    //endregion

    //region LIMIT

    public function limit(?int $limit): UpdateQuery
    {
        $this->limit = $limit;
        $this->built = false;
        return $this;
    }

    //endregion

    //region Execution

    public function exec(): int
    {
        $this->validateAndBuild();
        return $this->connection->execute($this->toSql(), $this->getParams());
    }

    /**
     *
     * @return void
     * @throws RuntimeException
     */
    private function validateAndBuild(): void
    {
        if ($this->connection === null) {
            throw new RuntimeException('The database connection instance must not be null.');
        }
        $this->build();
    }

    //endregion

    //region Query Building

    public function build(): UpdateQuery
    {
        if ($this->built) {
            return $this;
        }
        $this->sql = '';
        $this->params = [];
        $this->buildFrom();
        $this->buildJoin();
        $this->buildAssignment();
        $this->buildWhere();
        $this->buildOrderBy();
        $this->buildLimit();
        $this->built = true;
        return $this;
    }

    private function buildFrom(): void
    {
        $this->sql .= 'UPDATE ';
        if ($this->from) {
            $this->sql .= $this->from->toSql();
            $this->addParams($this->from->getParams());
        }
    }

    private function buildJoin(): void
    {
        if ($this->join) {
            $this->sql .= ' ' . $this->join->toSql();
            $this->addParams($this->join->getParams());
        }
    }

    private function buildAssignment(): void
    {
        $this->sql .= ' SET ';
        if ($this->assignment) {
            $this->sql .= $this->assignment->toSql();
            $this->addParams($this->assignment->getParams());
        }
    }

    private function buildWhere(): void
    {
        if ($this->where) {
            $this->sql .= ' WHERE ' . $this->where->toSql();
            $this->addParams($this->where->getParams());
        }
    }

    private function buildOrderBy(): void
    {
        if ($this->order) {
            $this->sql .= ' ORDER BY ' . $this->order->toSql();
            $this->addParams($this->order->getParams());
        }
    }

    private function buildLimit(): void
    {
        if ($this->limit !== null) {
            $this->sql .= ' LIMIT ' . $this->limit;
        }
    }

    //endregion

    public function toSql(): string
    {
        $this->build();
        return parent::toSql();
    }

    public function getParams(): array
    {
        $this->build();
        return parent::getParams();
    }
}