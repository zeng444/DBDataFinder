<?php

namespace Janfish\Database\Criteria;

use Janfish\Database\Criteria\Adapter\AdapterTrait;
use Janfish\Database\Criteria\Adapter\Mongo;
use Janfish\Database\Criteria\Adapter\Mysql;

/**
 *
 * Class Finder
 * @package Janfish\Database\Criteria
 */
class Finder
{

    const MYSQL_MODE = 'MYSQL';


    const MONGO_MODE = 'MONGO';
    /**
     *
     */
    const EQUAL_DIRECTIVE = 'eq';
    /**
     *
     */
    const REGEX_DIRECTIVE = 'regex';
    /**
     *
     */
    const GREATER_THAN_EQUAL_DIRECTIVE = 'gte';
    /**
     *
     */
    const LESS_THAN_EQUAL_DIRECTIVE = 'lte';
    /**
     *
     */
    const IN_DIRECTIVE = 'in';
    /**
     *
     */
    const NOT_EQUAL_DIRECTIVE = 'neq';
    /**
     *
     */
    const GREATER_THAN_DIRECTIVE = 'gt';
    /**
     *
     */
    const LESS_THAN_DIRECTIVE = 'lt';
    /**
     *
     */
    const NOT_IN_DIRECTIVE = 'notIn';

    /**
     *
     */
    const WHERE_DIRECTIVE = 'where';

    /**
     *
     */
    const CONDITION_DIRECTIVES = [
        self::WHERE_DIRECTIVE,
    ];

    /**
     * @var array
     */
    private static $aliasDirectives = [
        self::REGEX_DIRECTIVE => '$regex',
        self::EQUAL_DIRECTIVE => '$eq',
        self::NOT_EQUAL_DIRECTIVE => '$ne',
        self::IN_DIRECTIVE => '$in',
        self::NOT_IN_DIRECTIVE => '$nin',
        self::GREATER_THAN_DIRECTIVE => '$gt',
        self::LESS_THAN_DIRECTIVE => '$lt',
        self::GREATER_THAN_EQUAL_DIRECTIVE => '$gte',
        self::LESS_THAN_EQUAL_DIRECTIVE => '$lte',
        self::WHERE_DIRECTIVE => '$where',
    ];

    private $_aliasMap = [];
    /**
     * @var Mongo|Mysql
     */
    private $_adapter;

    /**
     * @var bool
     */
    private $_autoFullSearch;

    /**
     * @var
     */
    private $_hideColumns;

    /**
     * @var
     */
    private $_aliasColumns = [];
    /**
     * @var array
     */
    private $_flipAliasColumns = [];

    /**
     * Finder constructor.
     * @param string $mode
     * @param bool $autoFullSearch
     * @throws \Exception
     */
    public function __construct($mode = self::MYSQL_MODE, bool $autoFullSearch = true)
    {
        $this->_autoFullSearch = $autoFullSearch;
        $this->_adapter = $this->getAdapter($mode, $this->_autoFullSearch);
    }

    /**
     * @param string $mode
     * @param bool $autoFullSearch
     * @return Mongo|Mysql
     * @throws \Exception
     */
    private function getAdapter(string $mode, bool $autoFullSearch)
    {
        switch ($mode) {
            case self::MYSQL_MODE:
                $instance = new Mysql($autoFullSearch);
                break;
            case self::MONGO_MODE:
                $instance = new Mongo($autoFullSearch);
                break;
            default:
                throw new \Exception($mode.' Adapter NOT SUPPORT');
                break;
        }
        return $instance;
    }

    /**
     * @param array $directives
     */
    public function setAliasDirectives(array $directives)
    {
        foreach ($directives as $directive => $alias) {
            $this->setAliasDirective($directive, $alias);
        }
    }

    /**
     * @param string $directive
     * @param string $alias
     */
    public function setAliasDirective(string $directive, string $alias)
    {
        if (isset(self::$aliasDirectives[$directive])) {
            self::$aliasDirectives[$directive] = $alias;
        }
    }

    /**
     * Author:Robert
     *
     * @param array $rule
     * @return $this
     */
    public function setSort(array $rule)
    {
        $rules = [];
        foreach ($rule as $column => $val) {
            $rules[$this->getSourceColumn($column)] = $val;
        }
        $this->_adapter->setSort($rules);
        return $this;
    }

    /**
     * Author:Robert
     *
     * @param array $columns
     * @return $this
     */
    public function setColumns(array $columns)
    {
        foreach ($columns as &$val) {
            $val = $this->getSourceColumn($val);
        }
        $this->_adapter->setColumns($columns);
        return $this;
    }


    /**
     * 配置快捷查询命令
     * Author:Robert
     *
     * @param array $conditions
     * @return $this
     */
    public function setConditions(array $conditions)
    {
        $rules = [];
        foreach ($conditions as $column => $condition) {
            if (is_array($condition)) {
                $column = $this->getSourceColumn($column);
                if (in_array($column, $this->_adapter->dateColumns)) {
                    if (isset($condition[0]) && $condition[0]) {
                        $rules[$column][self::GREATER_THAN_EQUAL_DIRECTIVE] = $condition[0];
                    }
                    if (isset($condition[1]) && $condition[1]) {
                        $rules[$column][self::LESS_THAN_EQUAL_DIRECTIVE] = $condition[1];
                    }
                } else {
                    $key = key($condition);
                    if (is_int($key)) {
                        $rules[$column][self::IN_DIRECTIVE] = $condition;
                    } else {
                        $rules[$column][$this->getAliasDirective($key) ?: $key] = $condition[$key];
                    }
                }
            } else {
                $sourceColumn = $this->getSourceColumn($column);
                if ($this->_autoFullSearch && in_array($sourceColumn, $this->_adapter->fullTextColumns)) {
                    $rules[$sourceColumn][self::REGEX_DIRECTIVE] = $condition;
                } else {
                    $column = $this->getAliasDirective($column) ?: $column;
                    if (in_array($column, self::CONDITION_DIRECTIVES)) {
                        $rules[$column] = $condition;
                    } else {
                        $rules[$sourceColumn][self::EQUAL_DIRECTIVE] = $condition;
                    }
                }
            }
        }
        $this->_adapter->setConditions($rules);
        return $this;
    }

    /**
     * @param string $alias
     * @return string
     */
    private function getAliasDirective(string $alias): string
    {
        if (!$this->_aliasMap) {
            $this->_aliasMap = array_flip(self::$aliasDirectives);
        }
        return $this->_aliasMap[$alias] ?? '';
    }

    /**
     * Author:Robert
     *
     * @param string $column
     * @return string
     */
    private function getSourceColumn(string $column): string
    {
        return $this->_flipAliasColumns[$column] ?? $column;
    }

    /**
     * Author:Robert
     *
     * @param array $aliasMap
     * @return $this
     */
    public function defineAliasColumns(array $aliasMap)
    {
        $this->_aliasColumns = $aliasMap;
        $this->_flipAliasColumns = array_flip($this->_aliasColumns);
        return $this;
    }


    /**
     * Author:Robert
     *
     * @param array $columns
     * @return $this
     */
    public function defineTypeColumns(array $columns)
    {
        if (isset($columns['date'])) {
            $this->_adapter->defineDateColumns($columns['date']);
        }
        if (isset($columns['double'])) {
            $this->_adapter->defineDoubleColumns($columns['double']);
        }
        if (isset($columns['integer'])) {
            $this->_adapter->defineIntegerColumns($columns['integer']);
        }
        if (isset($columns['fullText'])) {
            $this->_adapter->defineFullTextColumns($columns['fullText']);
        }
        return $this;
    }

    /**
     * @param $method
     * @param $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if ($call = call_user_func_array(array($this->_adapter, $method), $parameters)) {
            return $call;
        }
    }

    /**
     * Author:Robert
     *
     * @return int
     * @throws \Exception
     */
    public function count(): int
    {
        return $this->_adapter->count();
    }

    /**
     * Author:Robert
     *
     * @return array
     */
    public function fetchAll(): array
    {
        try {
            return $this->afterFetchEvent($this->_adapter->execute());
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Author:Robert
     *
     * @return array
     */
    public function execute(): array
    {
        return $this->fetchAll();
    }


    /**
     * Author:Robert
     *
     * @return array
     */
    public function fetchOne(): array
    {
        $this->_adapter->setPagination(1);
        $item = $this->fetchAll();
        return $item ? current($item) : [];
    }

    /**
     * 定义隐藏的列，SELECT * 时不返回
     * Author:Robert
     *
     * @param array $columns
     * @return $this
     */
    public function defineHideColumns(array $columns)
    {
        $rules = [];
        foreach ($columns as $rule) {
            $rules[$this->getSourceColumn($rule)] = 0;
        }
        $this->_hideColumns = $rules;
        return $this;
    }

    /**
     * Author:Robert
     *
     * @param array $items
     * @return array
     */
    private function afterFetchEvent(array $items): array
    {
        if ($this->_hideColumns || $this->_aliasColumns) {
            return array_map(function ($item) {
                foreach ($this->_aliasColumns as $column => $aliasColumn) {
                    if (isset($item[$column])) {
                        $item[$aliasColumn] = $item[$column];
                        unset($item[$column]);
                    }
                }
                if ($this->_hideColumns) {
                    $item = array_diff_key($item, $this->_hideColumns);
                }
                return $item;
            }, $items);
        }
        return $items;
    }


    /**
     * Author:Robert
     *
     * @param string $schema
     * @return $this
     */
    public function setSchema(string $schema)
    {
        $this->_adapter->setSchema($schema);
        return $this;
    }

    /**
     * Author:Robert
     *
     * @param string $table
     * @return $this
     */
    public function setTable(string $table)
    {
        $this->_adapter->setTable($table);
        return $this;
    }

    /**
     * Author:Robert
     *
     * @param array $columns
     * @return $this
     */
    public function defineDoubleColumns(array $columns)
    {
        foreach ($columns as &$val) {
            $val = $this->getSourceColumn($val);
        }
        $this->_adapter->defineDoubleColumns($columns);
        return $this;
    }

    /**
     * Author:Robert
     *
     * @param array $columns
     * @return $this
     */
    public function defineIntegerColumns(array $columns)
    {
        foreach ($columns as &$val) {
            $val = $this->getSourceColumn($val);
        }
        $this->_adapter->defineIntegerColumns($columns);
        return $this;
    }

    /**
     * Author:Robert
     *
     * @param array $columns
     * @return $this
     */
    public function defineDateColumns(array $columns)
    {
        foreach ($columns as &$val) {
            $val = $this->getSourceColumn($val);
        }
        $this->_adapter->defineDateColumns($columns);
        return $this;
    }

    /**
     * Author:Robert
     *
     * @param array $columns
     * @return $this
     */
    public function defineFullTextColumns(array $columns)
    {
        foreach ($columns as &$val) {
            $val = $this->getSourceColumn($val);
        }
        $this->_adapter->defineFullTextColumns($columns);
        return $this;
    }

    /**
     * Author:Robert
     *
     * @param int $offset
     * @param int|null $limit
     * @return $this
     */
    public function setPagination(int $offset, int $limit = null)
    {
        $this->_adapter->setPagination($offset, $limit);
        return $this;
    }

    /**
     * Author:Robert
     *
     * @param $connection
     * @return $this
     */
    public function setConnection($connection)
    {
        $this->_adapter->setConnection($connection);
        return $this;
    }

    /**
     * Author:Robert
     *
     * @return $this
     */
    public function debug()
    {
        $this->_adapter->debug();
        return $this;
    }
}