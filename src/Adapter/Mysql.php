<?php

namespace Janfish\Database\Criteria\Adapter;

use Phalcon\Db;
use Phalcon\Di;

/**
 * 查询器
 * Author:Robert
 *
 * Class Finder
 * @package Janfish\Swoole\Criteria
 */
class Mysql implements AdapterInterface
{
    use AdapterTrait;

    /**
     * @var array
     */
    public $sql = [];

    /**
     * @var array
     */
    public $bind = [];

    /**
     * @return array
     * @throws \Exception
     */
    public function fetchOne(): array
    {
        $this->setPagination(1);
        return current($this->execute());

    }

    /**
     * @return array
     * @throws \Exception
     */
    final function execute(): array
    {
        $params = $this->generateParams();
        list($sqlData, $bind) = $params;
        $sql = sprintf('SELECT %s FROM %s %s %s %s', $sqlData['SELECT'], $sqlData['FROM'], $sqlData['WHERE'], $sqlData['ORDER'], $sqlData['LIMIT']);;
        if (!$this->dbInstance) {
            $di = Di::getDefault();
            $this->dbInstance = $di->get('db');
        }
        if (!$this->dbInstance) {
            throw new \Exception('db service not exist');
        }
        $items = $this->dbInstance->fetchAll($sql, Db::FETCH_ASSOC, $bind, [
            'offset' => \PDO::PARAM_INT,
            'limit' => \PDO::PARAM_INT,
        ]);
        if ($this->hideColumns) {
            return $this->removeHideColumns($items);
        }
        return $items;
    }

    /**
     * @return array
     */
    final function generateParams()
    {
        $conditions = new \Janfish\Database\Criteria\Adapter\Mysql\Condition($this->conditions, [
            'date' => $this->dateColumns,
            'fullText' => $this->fullTextColumns,
        ]);
        list($whereSql, $bind) = $conditions->generate();
        $columns = $this->makeColumnSQL();
        $schema = $this->schema ? "`{$this->schema}`." : '';
        $sort = $this->makeSortSQL();
        $this->sql['SELECT'] = $columns;
        $this->sql['FROM'] = "{$schema}`{$this->table}`";
        $this->sql['WHERE'] = $whereSql ? 'WHERE ' . $whereSql : '';
        $this->sql['ORDER'] = $sort ? 'ORDER BY ' . $sort : '';
        $this->sql['LIMIT'] = ":offset,:limit";
        $this->bind = array_merge($bind, [
            'offset' => $this->offset,
            'limit' => $this->limit,
        ]);
        return [$this->sql, $this->bind];
    }

    /**
     * Author:Robert
     *
     * @return string
     */
    private function makeColumnSQL()
    {
        if (!$this->columns) {
            return '*';
        }
        $columns = [];
        foreach ($this->columns as $field => $alias) {
            if (is_int($field)) {
                $columns[] = "`{$alias}`";
            } else {
                $columns[] = "`{$field}` AS `{$alias}`";
            }
        }
        return implode(',', $columns);
    }

    /**
     * Author:Robert
     *
     * @return string
     */
    private function makeSortSQL(): string
    {
        if (!$this->sort) {
            return '';
        }
        if (is_array($this->sort)) {
            $sql = [];
            foreach ($this->sort as $column => $command) {
                if (is_int($column)) {
                    $sql[] = "$command";
                } else {
                    $sql[] = "`$column` $command";
                }

            }
            return implode(',', $sql);
        } else {
            return $this->sort;
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function fetchAll(): array
    {
        return $this->execute();
    }

    /**
     * @param string $countKey
     * @return int
     * @throws \Exception
     */
    public function count(string $countKey = 'id'): int
    {
        $fetchParams = $this->generateParams();
        if (!$this->dbInstance) {
            $di = Di::getDefault();
            $this->dbInstance = $di->get('db');
        }
        if (!$this->dbInstance) {
            throw new \Exception('db service not exist');
        }
        list($sqlData, $bind) = $fetchParams;
        $sql = sprintf('SELECT COUNT(`%s`) AS `count` FROM %s %s LIMIT %s', $countKey, $sqlData['FROM'], $sqlData['WHERE'], $sqlData['LIMIT']);
        $bind['limit'] = 1;
        $bind['offset'] = 0;
        $result = $this->dbInstance->fetchOne($sql, Db::FETCH_ASSOC, $bind, [
            'offset' => \PDO::PARAM_INT,
            'limit' => \PDO::PARAM_INT,
        ]);
        return $result['count'];
    }


}
