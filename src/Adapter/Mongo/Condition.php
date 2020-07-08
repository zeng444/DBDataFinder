<?php

namespace Janfish\Database\Criteria\Adapter\Mongo;
class Condition
{

    /**
     *
     */
    const AND_OPERATOR_CHAR = 'AND';
    const EQUAL_DIRECTIVE = 'eq';
    const NOT_EQUAL_DIRECTIVE = 'neq';
    const GREATER_THAN_DIRECTIVE = 'gt';
    const LESS_THAN_DIRECTIVE = 'lt';
    const GREATER_THAN_EQUAL_DIRECTIVE = 'gte';
    const LESS_THAN_EQUAL_DIRECTIVE = 'lte';
    const IN_DIRECTIVE = 'in';
    const NOT_IN_DIRECTIVE = 'notIn';

    const DIRECTIVE_MAP = [
        self::EQUAL_DIRECTIVE => '$eq',
        self::NOT_EQUAL_DIRECTIVE => '$ne',
        self::GREATER_THAN_DIRECTIVE => '$gt',
        self::LESS_THAN_DIRECTIVE => '$lt',
        self::GREATER_THAN_EQUAL_DIRECTIVE => '$gte',
        self::LESS_THAN_EQUAL_DIRECTIVE => '$lte',
        self::IN_DIRECTIVE => '$in',
        self::NOT_IN_DIRECTIVE => '$nin',
    ];

    private $_conditions = [];
    private $_filter = [];

    public function __construct(array $conditions = [], array $definer = [])
    {

    }

    public function makeNotInFilter(array $value)
    {
        return [self::DIRECTIVE_MAP[self::NOT_IN_DIRECTIVE] => $value];
    }

    public function makeNeqFilter($value)
    {
        return [self::DIRECTIVE_MAP[self::NOT_EQUAL_DIRECTIVE] => $value];
    }

    public function makeGtFilter($value)
    {
        return [self::DIRECTIVE_MAP[self::GREATER_THAN_DIRECTIVE] => $value];
    }

    public function makeLtFilter($value)
    {
        return [self::DIRECTIVE_MAP[self::LESS_THAN_DIRECTIVE] => $value];
    }

    public function makeGteFilter($value)
    {
        return [self::DIRECTIVE_MAP[self::GREATER_THAN_EQUAL_DIRECTIVE] => $value];
    }

    public function makeLteFilter($value)
    {
        return [self::DIRECTIVE_MAP[self::LESS_THAN_EQUAL_DIRECTIVE] => $value];
    }

    public function generate()
    {
        //["id"=>'ddd']
        //["id"=>['in'=>[1,2]]]
        //["id"=>['notIn'=>[1,2]]]
        //["id"=>['eq'=>'22']
        //["id"=>['neq'=>'22']
        //["id"=>['gt'=>'22']
        //["id"=>['gte'=>'22']
        //["id"=>['lt'=>'22']
        //["id"=>['lte'=>'22']
        foreach ($this->_conditions as $column => $rule) {
            if (is_array($rule)) {
                if (is_int(key($rule))) {
                    $this->_filter[$column] = $this->makeInFilter($rule);
                } else {
                    foreach ($rule as $directive => $val) {
                        $funcName = "make" . ucfirst($directive) . "Filter";
                        if (method_exists($this, $funcName)) {
                            $this->_filter[$column] = $this->$funcName($directive, $val);
                        }
                    }
                }
            } elseif (is_string($rule)) {
                $this->_filter[$column] = $this->makeEqFilter($rule);
            }

        }
    }

    /**
     * @param array $value
     * @return array
     */
    public function makeInFilter(array $value)
    {
        return [self::DIRECTIVE_MAP[self::IN_DIRECTIVE] => $value];
    }

    public function makeEqFilter($value)
    {
        return [self::DIRECTIVE_MAP[self::EQUAL_DIRECTIVE] => $value];
    }
}