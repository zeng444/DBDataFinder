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

    public $conditions = [];
    public $sort;
    protected $offset = 0;
    protected $limit = 1;
    public $fullTextColumns = [];
    public $integerColumns = [];
    public $doubleColumns = [];
    public $dateColumns = [];
    public $schema;
    public $table;
    public $hideColumns;
    public $columns;

    /**
     * Author:Robert
     *
     * @param string $schema
     * @return $this
     */
    public function setSchema(string $schema)
    {
        $this->schema = $schema;
        return $this;
    }

    /**
     * 查询数据表
     * Author:Robert
     *
     * @param $table
     * @return $this
     */
    public function setTable($table)
    {
        $this->table = $table;
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
        $this->doubleColumns = $columns;
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
        $this->integerColumns = $columns;
        return $this;
    }

    /**
     * 设置日期字段
     * Author:Robert
     *
     * @param array $columns
     * @return $this
     */
    public function defineDateColumns(array $columns)
    {
        $this->dateColumns = $columns;
        return $this;
    }

    /**
     * 设置全文字段
     * Author:Robert
     *
     * @param array $columns
     * @return $this
     */
    public function defineFullTextColumns(array $columns)
    {
        $this->fullTextColumns = $columns;
        return $this;
    }

    /**
     * 定义隐藏的列，SELECT *时不返回
     * Author:Robert
     *
     * @param array $columns
     * @return $this
     */
    public function defineHideColumns(array $columns)
    {
        $this->hideColumns = $columns;
        return $this;
    }

    /**
     * Author:Robert
     *
     * @param array $items
     * @return array
     */
    public function removeHideColumns(array $items)
    {
        $rules = [];
        foreach ($this->hideColumns as $rule) {
            $rules[$rule] = 0;
        }
        return array_map(function ($item) use ($rules) {
            return array_diff_key($item, $rules);
        }, $items);
    }

    /**
     *
     * Author:Robert
     *
     * @param array $columns
     * @return $this
     */
    public function setColumns(array $columns)
    {
        $this->columns = $columns;
        return $this;
    }


    /**
     *
     * Author:Robert
     *
     * @param  $rule
     * @return $this
     */
    public function setSort(array $rule)
    {
        $this->sort = $rule;
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
        if ($limit === null) {
            $this->offset = 0;
            $this->limit = $offset;
        } else {
            $this->offset = $offset;
            $this->limit = $limit;
        }
        return $this;
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