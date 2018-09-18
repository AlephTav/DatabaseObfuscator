<?php

use Database\SqlBuilder\Expressions\ConditionalExpression;
use Database\SqlBuilder\Query;

class QueryTest extends AbstractQueryTest
{
    //region FROM

    public function testFromTableName(): void
    {
        $q = (new Query())
            ->from('some_table');

        $this->assertSame('SELECT * FROM some_table', $q->toSql());
    }

    public function testFromTableNameWithAlias(): void
    {
        $q = (new Query())
            ->from('some_table', 't');

        $this->assertSame('SELECT * FROM some_table t', $q->toSql());
    }

    public function testFromListOfTables(): void
    {
        $q = (new Query())
            ->from([
                'tab1',
                'tab2',
                'tab3'
            ]);

        $this->assertSame('SELECT * FROM tab1, tab2, tab3', $q->toSql());
    }

    public function testFromListOfTablesWithAliases(): void
    {
        $q = (new Query())
            ->from([
                'tab1' => 't1',
                'tab2' => 't2',
                'tab3' => 't3'
            ]);

        $this->assertSame('SELECT * FROM tab1 t1, tab2 t2, tab3 t3', $q->toSql());
    }

    public function testFromListOfTablesAppend(): void
    {
        $q = (new Query())
            ->from('t1')
            ->from('t2')
            ->from('t3');

        $this->assertSame('SELECT * FROM t1, t2, t3', $q->toSql());
    }

    public function testFromListOfTablesWithAliasesAppend(): void
    {
        $q = (new Query())
            ->from('tab1', 't1')
            ->from('tab2', 't2')
            ->from('tab3', 't3');

        $this->assertSame('SELECT * FROM tab1 t1, tab2 t2, tab3 t3', $q->toSql());
    }

    public function testFromRawExpression(): void
    {
        $q = (new Query())
            ->from(Query::raw('my_table AS t'));

        $this->assertSame('SELECT * FROM my_table AS t', $q->toSql());
    }

    public function testFromAnotherQuery(): void
    {
        $q = (new Query())
            ->from((new Query())->from('my_table'));

        $this->assertSame('SELECT * FROM (SELECT * FROM my_table)', $q->toSql());
    }

    public function testFromAnotherQueryWithAlias(): void
    {
        $q = (new Query())
            ->from(
                (new Query())->from('my_table'),
                't'
            );

        $this->assertSame('SELECT * FROM (SELECT * FROM my_table) t', $q->toSql());
    }

    public function testFromListOfQueries(): void
    {
        $q = (new Query())
            ->from([
                (new Query())->from('tab1'),
                (new Query())->from('tab2'),
                (new Query())->from('tab3')
            ]);

        $this->assertSame(
            'SELECT * FROM (SELECT * FROM tab1), (SELECT * FROM tab2), (SELECT * FROM tab3)',
            $q->toSql()
        );
    }

    public function testFromListOfQueriesWithAliases(): void
    {
        $q = (new Query())
            ->from([
                [(new Query())->from('tab1'), 't1'],
                [(new Query())->from('tab2'), 't2'],
                [(new Query())->from('tab3'), 't3']
            ]);

        $this->assertSame(
            'SELECT * FROM (SELECT * FROM tab1) t1, (SELECT * FROM tab2) t2, (SELECT * FROM tab3) t3',
            $q->toSql()
        );
    }

    public function testFromMixedSources(): void
    {
        $q = (new Query())
            ->from([
                [Query::raw('tab1'), 't1'],
                [Query::raw('tab1'), 't2'],
                'tab3' => 't3',
                [(new Query())->from('tab4'), '']
            ]);

        $this->assertSame(
            'SELECT * FROM tab1 t1, tab1 t2, tab3 t3, (SELECT * FROM tab4)',
            $q->toSql()
        );
    }

    //endregion

    //region SELECT

    public function testSelectListOfFields(): void
    {
        $q = (new Query())
            ->from('t')
            ->select([
                'f1',
                'f2',
                'f3'
            ]);

        $this->assertSame('SELECT f1, f2, f3 FROM t', $q->toSql());
    }

    public function testSelectListOfFieldsWithAlias(): void
    {
        $q = (new Query())
            ->from('t')
            ->select([
                'field1' => 'f1',
                'field2' => 'f2',
                'field3' => 'f3'
            ]);

        $this->assertSame('SELECT field1 f1, field2 f2, field3 f3 FROM t', $q->toSql());
    }

    public function testSelectListOfFieldsAppend(): void
    {
        $q = (new Query())
            ->from('t')
            ->select('field1')
            ->select('field2')
            ->select('field3');

        $this->assertSame('SELECT field1, field2, field3 FROM t', $q->toSql());
    }

    public function testSelectListOfFieldsWithAliasesAppend(): void
    {
        $q = (new Query())
            ->from('t')
            ->select('field1','t1')
            ->select('field2', 't2')
            ->select('field3', 't3');

        $this->assertSame('SELECT field1 t1, field2 t2, field3 t3 FROM t', $q->toSql());
    }

    public function testSelectStringExpression(): void
    {
        $q = (new Query())
            ->from('t')
            ->select('f1, f2, f3');

        $this->assertSame('SELECT f1, f2, f3 FROM t', $q->toSql());
    }

    public function testSelectRawExpression(): void
    {
        $q = (new Query())
            ->from('t')
            ->select(Query::raw('f1, f2, f3'));

        $this->assertSame('SELECT f1, f2, f3 FROM t', $q->toSql());
    }

    public function testSelectQuery(): void
    {
        $q = (new Query())
            ->from('t1')
            ->select((new Query())->from('t2'));

        $this->assertSame('SELECT (SELECT * FROM t2) FROM t1', $q->toSql());
    }

    public function testSelectQueryWithAlias(): void
    {
        $q = (new Query())
            ->from('tab1', 't1')
            ->select(
                (new Query())->from('tab2'),
                'f1'
            );

        $this->assertSame('SELECT (SELECT * FROM tab2) f1 FROM tab1 t1', $q->toSql());
    }

    public function testSelectMixedSources(): void
    {
        $q = (new Query())
            ->from('t1')
            ->select([
                [(new Query())->from('tab2'), 'f1'],
                [null, 'f2'],
                'field3' => 'f3',
                [Query::raw('COUNT(*)'), 'f4']
            ]);

        $this->assertSame(
            'SELECT (SELECT * FROM tab2) f1, NULL f2, field3 f3, COUNT(*) f4 FROM t1',
            $q->toSql()
        );
    }

    //endregion

    //region JOIN

    public function testJoinSingleTable(): void
    {
        $q = (new Query())
            ->from('tab1 t1')
            ->join('tab2 t2', 't2.id = t1.tab1_id');

        $this->assertSame('SELECT * FROM tab1 t1 JOIN tab2 t2 ON t2.id = t1.tab1_id', $q->toSql());
    }

    public function testJoinListOfTables(): void
    {
        $q = (new Query())
            ->from('tab1')
            ->join(['tab2', 'tab3'], 'tab2.id = tab3.id AND tab1.id = tab3.id');

        $this->assertSame(
            'SELECT * FROM tab1 JOIN (tab2, tab3) ON tab2.id = tab3.id AND tab1.id = tab3.id',
            $q->toSql()
        );
    }

    public function testJoinListOfTablesAppend(): void
    {
        $q = (new Query())
            ->from('t1')
            ->join('t2', 't2.id = t1.id')
            ->join('t3', 't3.id = t2.id')
            ->join('t4', ['t4.id', 't3.id']);

        $this->assertSame(
            'SELECT * FROM t1 JOIN t2 ON t2.id = t1.id JOIN t3 ON t3.id = t2.id JOIN t4 USING (t4.id, t3.id)',
            $q->toSql()
        );
    }

    public function testJoinListOfTablesWithAliases(): void
    {
        $q = (new Query())
            ->from('tab1', 't1')
            ->join(['tab2' => 't2', 'tab3' => 't3'], 't2.id = t3.id AND t1.id = t3.id');

        $this->assertSame(
            'SELECT * FROM tab1 t1 JOIN (tab2 t2, tab3 t3) ON t2.id = t3.id AND t1.id = t3.id',
            $q->toSql()
        );
    }

    public function testJoinTableWithColumnList(): void
    {
        $q = (new Query())
            ->from('t1')
            ->join('t2', ['f1', 'f2', 'f3']);

        $this->assertSame('SELECT * FROM t1 JOIN t2 USING (f1, f2, f3)', $q->toSql());
    }

    public function testJoinSubquery(): void
    {
        $q = (new Query())
            ->from('t1')
            ->join((new Query())->from('t2'), 't2.id = t1.id');

        $this->assertSame('SELECT * FROM t1 JOIN (SELECT * FROM t2) ON t2.id = t1.id', $q->toSql());
    }

    public function testJoinSubqueryWithAlias(): void
    {
        $q = (new Query())
            ->from('tab1', 't1')
            ->join([[(new Query())->from('tab2'), 't2']], 't2.id = t1.id');

        $this->assertSame(
            'SELECT * FROM tab1 t1 JOIN (SELECT * FROM tab2) t2 ON t2.id = t1.id',
            $q->toSql()
        );
    }

    public function testJoinTableWithNestedConditions(): void
    {
        $q = (new Query())
            ->from('t1')
            ->join('t2', function(ConditionalExpression $conditions) { $conditions
                ->with('t2.id', '=', Query::raw('t1.id'))
                ->and('t1.f1', '>', Query::raw('t2.f2'))
                ->or('t2.f3', '<>', Query::raw('t1.f3'));
            });

        $this->assertSame(
            'SELECT * FROM t1 JOIN t2 ON (t2.id = t1.id AND t1.f1 > t2.f2 OR t2.f3 <> t1.f3)',
            $q->toSql()
        );
    }

    public function testJoinOfDifferentTypes(): void
    {
        $q = (new Query())
            ->from('t1')
            ->innerJoin('t2')
            ->leftJoin('t3')
            ->leftOuterJoin('t4')
            ->naturalLeftJoin('t5')
            ->naturalLeftOuterJoin('t6')
            ->rightJoin('t7')
            ->rightOuterJoin('t8')
            ->naturalRightJoin('t9')
            ->naturalRightOuterJoin('t10')
            ->crossJoin('t11')
            ->straightJoin('t12');

        $this->assertSame(
            'SELECT * FROM t1 INNER JOIN t2 LEFT JOIN t3 LEFT OUTER JOIN t4 NATURAL LEFT JOIN t5 ' .
            'NATURAL LEFT OUTER JOIN t6 RIGHT JOIN t7 RIGHT OUTER JOIN t8 NATURAL RIGHT JOIN t9 ' .
            'NATURAL RIGHT OUTER JOIN t10 CROSS JOIN t11 STRAIGHT_JOIN t12',
            $q->toSql()
        );
    }

    //endregion

    //region LIMIT & OFFSET

    public function testLimit(): void
    {
        $q = (new Query())
            ->from('t')
            ->limit(10);

        $this->assertSame('SELECT * FROM t LIMIT 10', $q->toSql());
    }

    public function testOffset(): void
    {
        $q = (new Query())
            ->from('t')
            ->offset(12);

        $this->assertSame('SELECT * FROM t OFFSET 12', $q->toSql());
    }

    public function testLimitAndOffset(): void
    {
        $q = (new Query())
            ->from('t')
            ->limit(5)
            ->offset(12);

        $this->assertSame('SELECT * FROM t LIMIT 5 OFFSET 12', $q->toSql());
    }

    public function testPage(): void
    {
        $q = (new Query())
            ->from('t')
            ->paginate(3, 7);

        $this->assertSame('SELECT * FROM t LIMIT 7 OFFSET 21', $q->toSql());
    }

    //endregion
}