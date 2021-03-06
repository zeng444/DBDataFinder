<?php

namespace Janfish\Database\Criteria\Adapter;

use Janfish\Database\Criteria\Finder;
use MongoDB\Operation\Find;
use Phalcon\Di;

/**
 * Class Mongo
 * @package Janfish\Database\Criteria\Adapter
 */
class Mongo implements AdapterInterface, DirectiveInterface
{
    /**
     *
     */
    use AdapterTrait;

    /**
     *
     */
    const DEFAULT_PRIMARY_ID_NAME = '_id';
    /**
     *
     */
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
        Finder::WHERE_DIRECTIVE => '$where',
    ];
    /**
     * @var bool
     */
    private $_autoFullSearch = true;
    /**
     * @var
     */
    private $_connection;

    public function __construct(bool $autoFullSearch = true)
    {
        $this->_autoFullSearch = $autoFullSearch;
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
     * @param string $field
     * @param string $val
     * @return float|int|\MongoDB\BSON\ObjectId|\MongoDB\BSON\Regex|\MongoDB\BSON\UTCDateTime|string
     */
    private function formatValue(string $field, $val)
    {
        if ($field === self::DEFAULT_PRIMARY_ID_NAME) {
            $val = new \MongoDB\BSON\ObjectId($val);
        } elseif ($this->_autoFullSearch && in_array($field, $this->fullTextColumns)) {
            $val = new \MongoDB\BSON\Regex(preg_quote($val));
        } elseif (in_array($field, $this->dateColumns)) {
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
    public function makeNeqFilter(string $field, $value)
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
    public function makeEqFilter(string $field, $value)
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
    public function makeRegexFilter(string $field, $value)
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
    public function makeGtFilter(string $field, $value)
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
    public function makeLtFilter(string $field, $value)
    {
        return [self::DIRECTIVE_MAP[Finder::LESS_THAN_DIRECTIVE], $this->formatValue($field, $value)];
    }


    /**
     * @param string $directive
     * @param string $value
     * @return array
     */
    public function makeWhereFilter(string $directive, $value)
    {
        return [self::DIRECTIVE_MAP[Finder::WHERE_DIRECTIVE], $value];
    }

    /**
     * Author:Robert
     *
     * @param $field
     * @param $value
     * @return array
     */
    public function makeGteFilter(string $field, $value)
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
    public function makeLteFilter(string $field, $value)
    {
        return [self::DIRECTIVE_MAP[Finder::LESS_THAN_EQUAL_DIRECTIVE], $this->formatValue($field, $value)];
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function count(): int
    {
        $filter = $this->getFilters();
        $mongo = $this->getConnection()->selectDatabase($this->schema);
        $collection = $mongo->selectCollection($this->table);
        return $collection->count($filter);
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
            if (in_array($column, Finder::CONDITION_DIRECTIVES)) {
                $filters[self::DIRECTIVE_MAP[$column]] = $rule;
            } else {
                foreach ($rule as $directive => $val) {
                    $funcName = "make" . ucfirst($directive) . "Filter";
                    if (method_exists($this, $funcName)) {
                        $symbol = $this->$funcName($column, $val);
                        $filters[$column][$symbol[0]] = $symbol[1];
                    }
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
    private function getConnection()
    {
        if ($this->_connection) {
            return $this->_connection;
        }
        $this->setConnection();
        if (!$this->_connection) {
            throw new \Exception('db service not exist');
        }
        return $this->_connection;
    }

    /**
     * @param null $connection
     */
    public function setConnection($connection = null)
    {
        if ($connection) {
            $this->_connection = $connection;
        } else {
            $this->_connection = (Di::getDefault())->get('mongo');
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function execute(): array
    {
        $filter = $this->getFilters();
        $mongo = $this->getConnection()->selectDatabase($this->schema);
        $collection = $mongo->selectCollection($this->table);
        $cursor = $collection->find($filter, [
            'projection' => $this->makeColumnRule(),
            'skip' => $this->offset,
            'limit' => $this->limit,
            'sort' => $this->makeSortRule(),
        ]);
        $data = [];
        foreach ($cursor as $items) {
            $loops = (array)$items;
            foreach ($this->dateColumns as $col) {
                if (isset($loops[$col])) {
                    $loops[$col] = $this->formatDateTimeOutValue($col, $loops[$col]);
                }
            }
            if (isset($loops[self::DEFAULT_PRIMARY_ID_NAME])) {
                $loops[self::DEFAULT_PRIMARY_ID_NAME] = $this->formatPrimaryIdOutValue(self::DEFAULT_PRIMARY_ID_NAME, $loops[self::DEFAULT_PRIMARY_ID_NAME]);
            }
            $data[] = $loops;
        }
        return $data;
    }

    /**
     * @return array
     */
    private function makeColumnRule(): array
    {
        $columns = [];
        foreach ($this->columns as $field) {
            $columns[$field] = 1;
        }
        return $columns;
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

    private function formatDateTimeOutValue(string $field, \MongoDB\BSON\UTCDateTime $val, string $dateFormat = 'Y-m-d H:i:s'): string
    {
        return date($dateFormat, strtotime($val->toDateTime()
            ->setTimezone(new \DateTimeZone('PRC'))
            ->format(DATE_RSS)));;
    }

    /**
     * @param string $field
     * @param \MongoDB\BSON\ObjectId $val
     * @return string
     */
    private function formatPrimaryIdOutValue(string $field, \MongoDB\BSON\ObjectId $val): string
    {
        return $val->__toString();
    }

    public function debug()
    {
        return $this->getFilters();
    }

}