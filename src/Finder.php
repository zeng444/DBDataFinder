<?php

namespace Janfish\Database\Criteria;

use Janfish\Database\Criteria\Adapter\Mongo;
use Janfish\Database\Criteria\Adapter\Mysql;

/**
 *
 * Class Finder
 * @package Janfish\Database\Criteria
 * @method setSchema(string $schema): this
 * @method setTable(string $schema)
 * @method defineDoubleColumns(array $schema)
 * @method defineIntegerColumns(array $schema)
 * @method defineDateColumns(array $schema)
 * @method defineFullTextColumns(array $schema)
 * @method defineHideColumns(array $schema)
 * @method removeHideColumns(array $schema)
 * @method setColumns(array $schema)
 * @method setSort(array $schema)
 * @method setPagination(int $offset, int $limit = null)
 * @method count(string $primaryId = null): int
 * @method fetchOne(): array
 * @method fetchAll(): array
 */
class Finder
{

    const MYSQL_MODE = 'MYSQL';


    const MONGO_MODE = 'MONGO';

    /**
     * @var Mongo|Mysql
     */
    private $_instance;


    const EQUAL_DIRECTIVE = 'eq';
    const REGEX_DIRECTIVE = 'regex';
    const GREATER_THAN_EQUAL_DIRECTIVE = 'gte';
    const LESS_THAN_EQUAL_DIRECTIVE = 'lte';
    const IN_DIRECTIVE = 'in';
    const NOT_EQUAL_DIRECTIVE = 'neq';
    const GREATER_THAN_DIRECTIVE = 'gt';
    const LESS_THAN_DIRECTIVE = 'lt';
    const NOT_IN_DIRECTIVE = 'notIn';

    /**
     * Finder constructor.
     * @param string $mode
     * @throws \Exception
     */
    public function __construct($mode = self::MYSQL_MODE)
    {
        $this->_instance = $this->getAdapter($mode);
    }

    /**
     * @param string $mode
     * @return Mongo|Mysql
     * @throws \Exception
     */
    private function getAdapter(string $mode)
    {
        switch ($mode) {
            case self::MYSQL_MODE:
                $instance = new Mysql();
                break;
            case self::MONGO_MODE:
                $instance = new Mongo();
                break;
            default:
                throw new \Exception('DB FINDER HAD  NO DRIVER');
                break;
        }
        return $instance;
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
                if (in_array($column, $this->_instance->dateColumns)) {
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
                        $rules[$column][$key] = $condition[$key];
                    }
                }
            } else {
                if (in_array($column, $this->_instance->fullTextColumns)) {
                    $rules[$column][self::REGEX_DIRECTIVE] = $condition;
                } else {
                    $rules[$column][self::EQUAL_DIRECTIVE] = $condition;
                }
            }
        }
        $this->_instance->setConditions($rules);
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
            $this->_instance->defineDateColumns($columns['date']);
        }
        if (isset($columns['double'])) {
            $this->_instance->defineDoubleColumns($columns['double']);
        }
        if (isset($columns['integer'])) {
            $this->_instance->defineIntegerColumns($columns['integer']);
        }
        if (isset($columns['fullText'])) {
            $this->_instance->defineFullTextColumns($columns['fullText']);
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
        if ($call = call_user_func_array(array($this->_instance, $method), $parameters)) {
            return $call;
        }
    }

}