<?php

namespace Janfish\Database\Criteria\Adapter;

use Janfish\Database\Criteria\Finder;
use Phalcon\Di;

class Mongo implements AdapterInterface, DirectiveInterface
{
    use AdapterTrait;

    private $_mongo;

    const DIRECTIVE_MAP = [
        Finder::EQUAL_DIRECTIVE => '$eq',
        Finder::REGEX_DIRECTIVE => '$regex',
        Finder::NOT_EQUAL_DIRECTIVE => '$ne',
        Finder::GREATER_THAN_DIRECTIVE => '$gt',
        Finder::LESS_THAN_DIRECTIVE => '$lt',
        Finder::GREATER_THAN_EQUAL_DIRECTIVE => '$gte',
        Finder::LESS_THAN_EQUAL_DIRECTIVE => '$lte',
        Finder::IN_DIRECTIVE => '$in',
        Finder::NOT_IN_DIRECTIVE => '$nin',
    ];

    /**
     * Author:Robert
     *
     * @param string $field
     * @param string $val
     * @return float|int|\MongoDB\BSON\ObjectId|\MongoDB\BSON\Regex|\MongoDB\BSON\UTCDateTime|string
     */
    private function formatValue(string $field, string $val)
    {
        if ($field === '_id') {
            $val = new \MongoDB\BSON\ObjectId($val);
        } elseif (in_array($val, $this->fullTextColumns)) {
            $val = new \MongoDB\BSON\Regex(preg_quote($val));
        } elseif (in_array($val, $this->dateColumns)) {
            $val = new \MongoDB\BSON\UTCDateTime(strtotime($val) * 1000);
        } elseif (in_array($field, $this->integerColumns)) {
            $val = (int)$val;
        } elseif (in_array($field, $this->doubleColumns)) {
            $val = (double)$val;
        }
        return $val;
    }

    /**
     * Author:Robert
     *
     * @param $field
     * @param $value
     * @return array
     */
    public function makeInFilter(string $field, array $value)
    {
        foreach ($value as &$val) {
            $val = $this->formatValue($field, $val);
        }
        return [self::DIRECTIVE_MAP[Finder::IN_DIRECTIVE], $value];
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
        foreach ($value as &$val) {
            $val = $this->formatValue($field, $val);
        }
        return [self::DIRECTIVE_MAP[Finder::NOT_IN_DIRECTIVE], $value];
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
        return [self::DIRECTIVE_MAP[Finder::NOT_EQUAL_DIRECTIVE], $this->formatValue($field, $value)];
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
        return [self::DIRECTIVE_MAP[Finder::EQUAL_DIRECTIVE], $this->formatValue($field, $value)];
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
        return [self::DIRECTIVE_MAP[Finder::REGEX_DIRECTIVE], $this->formatValue($field, $value)];
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
        return [self::DIRECTIVE_MAP[Finder::GREATER_THAN_DIRECTIVE], $this->formatValue($field, $value)];
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
        return [self::DIRECTIVE_MAP[Finder::LESS_THAN_DIRECTIVE], $this->formatValue($field, $value)];
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
        return [self::DIRECTIVE_MAP[Finder::GREATER_THAN_EQUAL_DIRECTIVE], $this->formatValue($field, $value)];
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
        return [self::DIRECTIVE_MAP[Finder::LESS_THAN_EQUAL_DIRECTIVE], $this->formatValue($field, $value)];
    }

    /**
     * Author:Robert
     *
     * @return array
     */
    private function getFilters()
    {
        //生成表达式
        $filters = [];
        foreach ($this->conditions as $column => $rule) {
            foreach ($rule as $directive => $val) {
                $funcName = "make".ucfirst($directive)."Filter";
                if (method_exists($this, $funcName)) {
                    $symbol = $this->$funcName($column, $val);
                    $filters[$column][$symbol[0]] = $symbol[1];
                }
            }
        }
        return $filters;
    }

    /**
     * Author:Robert
     *
     * @return mixed
     * @throws \Exception
     */
    private function getMongoConnection()
    {
        if ($this->_mongo) {
            return $this->_mongo;
        }
        $this->_mongo = (Di::getDefault())->get('db');
        if (!$this->_mongo) {
            throw new \Exception('db service not exist');
        }
        return $this->_mongo;
    }


    /**
     * Author:Robert
     *
     * @return array
     */
    private function makeSortRule(): array
    {
        $rule = [];
        if (!$this->sort) {
            return $rule;
        }
        foreach ($this->sort as $column => $command) {
            $rule[$column] = $command === 'ASC' ? 1 : -1;
        }
        return $rule;
    }

    /**
     * Author:Robert
     *
     * @return int
     */
    public function count(): int
    {
        $filter = $this->getFilters();
        $mongo = $this->getDi()->get('mongo');
        $collection = $mongo->selectDatabase($this->schema);
        return $collection->count($filter);
    }

    /**
     * Author:Robert
     *
     * @return array
     */
    private function execute()
    {
        $filter = $this->getFilters();
        $mongo = $this->getDi()->get('mongo');
        $collection = $mongo->selectDatabase($this->schema);
        $items = $collection->find($filter, [
            'projection' => $this->makeSortRule(),
            'skip' => $this->offset,
            'limit' => $this->limit,
            'sort' => [],
        ]);
        if ($this->hideColumns) {
            return $this->removeHideColumns($items);
        }
        return $items;
    }

    /**
     * Author:Robert
     *
     * @return array
     */
    public function fetchOne(): array
    {
        $this->setPagination(1);
        $item = $this->execute();
        return $item ? current($item) : [];
    }


    /**
     * Author:Robert
     *
     * @return array
     */
    public function fetchAll(): array
    {
        return $this->execute();
    }
}