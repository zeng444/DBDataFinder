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

    public function setSchema(string $schema);

    public function setTable($table);

    public function defineDoubleColumns(array $columns);

    public function defineIntegerColumns(array $columns);

    public function defineDateColumns(array $columns);

    public function defineFullTextColumns(array $columns);

    public function defineHideColumns(array $columns);

    public function removeHideColumns(array $columns);

    public function setPagination(int $offset, int $limit = null);

    public function setConditions(array $conditions);

    public function setColumns(array $columns);

    public function setSort(array $rule);

    public function count(): int;

    public function fetchOne(): array;

    public function fetchAll(): array;

}