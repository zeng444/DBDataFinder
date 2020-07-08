<?php

namespace Janfish\Database\Criteria;

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
     * @var Mongo|Mysql
     */
    private $_instance;

    /**
     * Finder constructor.
     * @param string $mode
     * @throws \Exception
     */
    public function __construct($mode = self::MYSQL_MODE)
    {
        $this->_instance = $this->getInstance($mode);
    }

    /**
     * @param string $mode
     * @return Mongo|Mysql
     * @throws \Exception
     */
    private function getInstance(string $mode)
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