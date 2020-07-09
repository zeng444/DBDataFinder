<?php

namespace Janfish\Database\Criteria\Adapter;

/**
 * Author:Robert
 *
 * Trait AdapterTrait
 * @package Janfish\Database\Criteria\Adapter
 */
trait AdapterTrait
{
    /**
     * @var array
     */
    public $conditions = [];

    /**
     * @var
     */
    public $sort;
    /**
     * @var array
     */
    public $fullTextColumns = [];
    /**
     * @var array
     */
    public $integerColumns = [];
    /**
     * @var array
     */
    public $doubleColumns = [];
    /**
     * @var array
     */
    public $dateColumns = [];
    /**
     * @var
     */
    public $schema;
    /**
     * @var
     */
    public $table;

    /**
     * @var
     */
    public $columns = [];
    /**
     * @var int
     */
    protected $offset = 0;
    /**
     * @var int
     */
    protected $limit = 1;

    /**
     * Author:Robert
     *
     * @param string $schema
     */
    public function setSchema(string $schema)
    {
        $this->schema = $schema;
    }

    /**
     * 查询数据表
     * Author:Robert
     *
     * @param $table
     */
    public function setTable(string $table)
    {
        $this->table = $table;
    }


    /**
     * Author:Robert
     *
     * @param array $columns
     */
    public function defineDoubleColumns(array $columns)
    {
        $this->doubleColumns = $columns;
    }

    /**
     * Author:Robert
     *
     * @param array $columns
     */
    public function defineIntegerColumns(array $columns)
    {
        $this->integerColumns = $columns;
    }

    /**
     * 设置日期字段
     * Author:Robert
     *
     * @param array $columns
     */
    public function defineDateColumns(array $columns)
    {
        $this->dateColumns = $columns;
    }

    /**
     * 设置全文字段
     * Author:Robert
     *
     * @param array $columns
     */
    public function defineFullTextColumns(array $columns)
    {
        $this->fullTextColumns = $columns;
    }


    /**
     *
     * Author:Robert
     *
     * @param array $columns
     */
    public function setColumns(array $columns)
    {
        $this->columns = $columns;
    }


    /**
     *
     * Author:Robert
     *
     * @param  $rule
     */
    public function setSort(array $rule)
    {
        $this->sort = $rule;
    }

    /**
     * Author:Robert
     *
     * @param int $offset
     * @param int|null $limit
     */
    public function setPagination(int $offset, int $limit = null)
    {
        if ($limit === null) {
            $this->offset = 0;
            $this->limit = $offset;
        } else {
            $this->offset = $offset;
            $this->limit = $limit;
        }
    }


    /**
     * Author:Robert
     *
     * @param array $conditions
     */
    public function setConditions(array $conditions)
    {
        $this->conditions = $conditions;
    }

}