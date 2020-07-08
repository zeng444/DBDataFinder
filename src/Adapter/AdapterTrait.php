<?php

namespace Janfish\Database\Criteria\Adapter;

trait AdapterTrait
{

    protected $schema;
    protected $table;
    protected $columns;
    protected $dateColumns;
    protected $fullTextColumns;
    protected $integerColumns;
    protected $doubleColumns;
    protected $conditions;
    protected $sort;
    protected $hideColumns;
    protected $limit;
    protected $offset;
    public $dbInstance;

    public function defineDoubleColumns(array $columns)
    {
        $this->doubleColumns = $columns;
        return $this;
    }

    public function defineIntColumns(array $columns)
    {
        $this->integerColumns = $columns;
        return $this;
    }


    public function setSchema(string $schema)
    {
        $this->schema = $schema;
        return $this;
    }

    /**
     * 设置返回的列
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


    public function setDbService($db)
    {
        $this->dbInstance = $db;
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
     * Author:Robert
     *
     * @param array $columns
     * @return $this
     */
    public function defineTypeColumns(array $columns)
    {
        if (isset($columns['date'])) {
            $this->defineDateColumns($columns['date']);
        }
        if (isset($columns['fullText'])) {
            $this->defineFullTextColumns($columns['fullText']);
        }
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
     * 设置查询条件
     * Author:Robert
     *
     * @param array $conditions
     * @return $this
     */
    public function setConditions(array $conditions)
    {
        $this->conditions = $conditions;
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
     * 设置排序
     * Author:Robert
     *
     * @param  $rule
     * @return $this
     */
    public function setSort($rule)
    {
        $this->sort = $rule;
        return $this;
    }


    public function setPagination($offset, $limit = null)
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

}