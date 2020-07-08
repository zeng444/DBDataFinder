<?php

namespace Janfish\Database\Criteria\Adapter;

use Janfish\Database\Criteria\Finder;
use Phalcon\Di;

class Mongo implements AdapterInterface,DirectiveInterface
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


    public function makeInFilter( $value)
    {
        return [self::DIRECTIVE_MAP[Finder::IN_DIRECTIVE],$value];
    }

    public function makeNotInFilter( $value)
    {
        return [self::DIRECTIVE_MAP[Finder::NOT_IN_DIRECTIVE] , $value];
    }

    public function makeNeqFilter($value)
    {
        return [self::DIRECTIVE_MAP[Finder::NOT_EQUAL_DIRECTIVE] , $value];
    }

    public function makeEqFilter($value)
    {
        return [self::DIRECTIVE_MAP[Finder::EQUAL_DIRECTIVE] , $value];
    }

    public function makeRegexFilter($value){
        return [self::DIRECTIVE_MAP[Finder::REGEX_DIRECTIVE] , $value];
    }

    public function makeGtFilter($value)
    {
        return [self::DIRECTIVE_MAP[Finder::GREATER_THAN_DIRECTIVE] , $value];
    }

    public function makeLtFilter($value)
    {
        return [self::DIRECTIVE_MAP[Finder::LESS_THAN_DIRECTIVE] , $value];
    }

    public function makeGteFilter($value)
    {
        return [self::DIRECTIVE_MAP[Finder::GREATER_THAN_EQUAL_DIRECTIVE] , $value];
    }

    public function makeLteFilter($value)
    {
        return [self::DIRECTIVE_MAP[Finder::LESS_THAN_EQUAL_DIRECTIVE] , $value];
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
                    $symbol = $this->$funcName( $val);
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
        if($this->_mongo){
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
    private function makeSortRule():array
    {
        $rule = [];
        if (!$this->sort) {
            return $rule;
        }
        foreach ($this->sort as $column => $command) {
            $rule[$column] = $command ==='ASC'?1:-1;
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
        $filter =  $this->getFilters();
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
        $filter =  $this->getFilters();
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
        return $item ? current($item):[];
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