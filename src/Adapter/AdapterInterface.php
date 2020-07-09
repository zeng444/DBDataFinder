<?php

namespace Janfish\Database\Criteria\Adapter;

/**
 * Author:Robert
 *
 * Interface AdapterInterface
 * @package Janfish\Database\Criteria\Adapter
 */
interface AdapterInterface
{

    /**
     * @param string $schema
     * @return mixed
     */
    public function setSchema(string $schema);

    /**
     * @param string $table
     * @return mixed
     */
    public function setTable(string $table);

    /**
     * @param array $columns
     * @return mixed
     */
    public function defineDoubleColumns(array $columns);

    /**
     * @param array $columns
     * @return mixed
     */
    public function defineIntegerColumns(array $columns);

    /**
     * @param array $columns
     * @return mixed
     */
    public function defineDateColumns(array $columns);

    /**
     * @param array $columns
     * @return mixed
     */
    public function defineFullTextColumns(array $columns);


    /**
     * @param int $offset
     * @param int|null $limit
     * @return mixed
     */
    public function setPagination(int $offset, int $limit = null);

    /**
     * @param array $conditions
     * @return mixed
     */
    public function setConditions(array $conditions);

    /**
     * @param array $columns
     * @return mixed
     */
    public function setColumns(array $columns);

    /**
     * @param array $rule
     * @return mixed
     */
    public function setSort(array $rule);

    /**
     * @return int
     */
    public function count(): int;


    /**
     * @return array
     */
    public function execute(): array;

    /**
     * @return mixed
     */
    public function debug();

    /**
     * @param null $connection
     * @return mixed
     */
    public function setConnection($connection = null);

}