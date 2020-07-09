<?php

namespace Janfish\Database\Criteria\Adapter;

use Phalcon\Db;
use Phalcon\Di;

/**
 * Class Mysql
 * @package Janfish\Database\Criteria\Adapter
 */
class Mysql implements AdapterInterface, DirectiveInterface
{
    use AdapterTrait;

    private $_holderCharIndex = 0;

    /**
     * @var
     */
    private $_db;

    /**
     * Author:Robert
     *
     * @param $field
     * @param $value
     * @return array
     */
    public function makeInFilter(string $field, array $value)
    {
        $holders = [];
        $bind = [];
        foreach ($value as $val) {
            $holder = $this->generateHolderPlaceChar();
            $holders[] = ':' . $holder;
            $bind[$holder] = $val;
        }
        $sql = "`$field` IN (" . implode(',', $holders) . ")";
        return [$sql, $bind];
    }

    /**
     * Author:Robert
     *
     * @return string
     */
    private function generateHolderPlaceChar()
    {
        $this->_holderCharIndex++;
        return 'h' . $this->_holderCharIndex;
    }

    /**
     * Author:Robert
     *
     * @param $field
     * @param $value
     * @return array
     */
    public function makeNotInFilter(string $field, array $value)
    {
        $holders = [];
        $bind = [];
        foreach ($value as $val) {
            $holder = $this->generateHolderPlaceChar();
            $holders[] = ':' . $holder;
            $bind[$holder] = $val;
        }
        $sql = "`$field` NOT IN (" . implode(',', $holders) . ")";
        return [$sql, $bind];
    }

    /**
     * Author:Robert
     *
     * @param $field
     * @param $value
     * @return array
     */
    public function makeNeqFilter(string $field, string $value)
    {
        $holder = $this->generateHolderPlaceChar();
        $sql = "`$field` <> :$holder";
        return [$sql, [$holder => $value]];
    }

    /**
     * Author:Robert
     *
     * @param $field
     * @param $value
     * @return array
     */
    public function makeRegexFilter(string $field, string $value)
    {
        $holder = $this->generateHolderPlaceChar();
        $sql = "`$field` LIKE :$holder";
        return [$sql, [$holder => "%$value%"]];
    }

    /**
     * Author:Robert
     *
     * @param $field
     * @param $value
     * @return array
     */
    public function makeEqFilter(string $field, string $value)
    {
        $holder = $this->generateHolderPlaceChar();
        $sql = "`$field` = :$holder";
        return [$sql, [$holder => $value]];
    }

    /**
     * Author:Robert
     *
     * @param $field
     * @param $value
     * @return array
     */
    public function makeGtFilter(string $field, string $value)
    {
        $holder = $this->generateHolderPlaceChar();
        $sql = "`$field` >= :$holder";
        return [$sql, [$holder => $value]];
    }

    /**
     * Author:Robert
     *
     * @param $field
     * @param $value
     * @return array
     */
    public function makeLtFilter(string $field, string $value)
    {
        $holder = $this->generateHolderPlaceChar();
        $sql = "`$field` <= :$holder";
        return [$sql, [$holder => $value]];
    }

    /**
     * Author:Robert
     *
     * @param $field
     * @param $value
     * @return array
     */
    public function makeGteFilter(string $field, string $value)
    {
        $holder = $this->generateHolderPlaceChar();
        $sql = "`$field` >= :$holder";
        return [$sql, [$holder => $value]];
    }

    /**
     * Author:Robert
     *
     * @param $field
     * @param $value
     * @return array
     */
    public function makeLteFilter(string $field, string $value)
    {
        $holder = $this->generateHolderPlaceChar();
        $sql = "`$field` <= :$holder";
        return [$sql, [$holder => $value]];
    }

    /**
     * Author:Robert
     *
     * @param string $primaryId
     * @return int
     * @throws \Exception
     */
    public function count(string $primaryId = 'id'): int
    {
        $fetchParams = $this->getFilters();
        $table = $this->getSchemaTable();
        $where = $fetchParams[0];
        $where = $where ? 'WHERE ' . $where : '';
        $limit = ":offset,:limit";
        $sql = sprintf('SELECT COUNT(`%s`) AS `count` FROM %s %s LIMIT %s', $primaryId, $table, $where, $limit);
        $bind = array_merge($fetchParams[1], [
            'offset' => 0,
            'limit' => 1,
        ]);
        $db = $this->getDbConnection();
        $result = $db->fetchOne($sql, Db::FETCH_ASSOC, $bind, [
            'offset' => \PDO::PARAM_INT,
            'limit' => \PDO::PARAM_INT,
        ]);
        return $result['count'];
    }

    /**
     * Author:Robert
     *
     * @return array
     */
    private function getFilters()
    {
        $sql = [];
        $bind = [];
        foreach ($this->conditions as $column => $rules) {
            foreach ($rules as $directive => $val) {
                $funcName = "make" . ucfirst($directive) . "Filter";
                if (method_exists($this, $funcName)) {
                    $symbol = $this->$funcName($column, $val);
                    $sql[] = $symbol[0];
                    if ($symbol[1]) {
                        $bind = array_merge($bind, $symbol[1]);
                    }
                }
            }
        }
        $sql = implode(' AND ', $sql);
        return [$sql, $bind];
    }

    /**
     * @return string
     */
    private function getSchemaTable(): string
    {
        $schema = $this->schema ? "`{$this->schema}`." : '';
        return "{$schema}`{$this->table}`";
    }

    /**
     * Author:Robert
     *
     * @return mixed
     * @throws \Exception
     */
    private function getDbConnection()
    {
        if ($this->_db) {
            return $this->_db;
        }
        $this->_db = (Di::getDefault())->get('db');
        if (!$this->_db) {
            throw new \Exception('db service not exist');
        }
        return $this->_db;
    }

    /**
     * Author:Robert
     *
     * @return array
     * @throws \Exception
     */
    public function fetchOne(): array
    {
        $this->setPagination(1);
        $item = $this->execute();
        return $item ? current($item) : [];
    }

    public function debug()
    {
        return $this->getFilters();
    }

    /**
     * Author:Robert
     *
     * @throws \Exception
     */
    private function execute(): array
    {
        $fetchParams = $this->getFilters();
        $column = $this->makeColumnRule();
        $table = $this->getSchemaTable();
        $where = $fetchParams[0];
        $where = $where ? 'WHERE ' . $where : '';
        $sort = $this->makeSortRule();
        $limit = ":offset,:limit";
        $sql = sprintf('SELECT %s FROM %s %s %s LIMIT %s', $column, $table, $where, $sort, $limit);
        $bind = array_merge($fetchParams[1], [
            'offset' => $this->offset,
            'limit' => $this->limit,
        ]);
        $db = $this->getDbConnection();
        $items = $db->fetchAll($sql, Db::FETCH_ASSOC, $bind, [
            'offset' => \PDO::PARAM_INT,
            'limit' => \PDO::PARAM_INT,
        ]);
        if ($this->hideColumns) {
            return $this->removeHideColumns($items);
        }
        return $items;
    }

    /**
     * Author:Robert
     *
     * @return string
     */
    private function makeColumnRule(): string
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
    private function makeSortRule(): string
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
            $sort = implode(',', $sql);
        } else {
            $sort = $this->sort;
        }
        return $sort ? 'ORDER BY ' . $sort : '';
    }

    /**
     * Author:Robert
     *
     * @return array
     * @throws \Exception
     */
    public function fetchAll(): array
    {
        return $this->execute();
    }


}
