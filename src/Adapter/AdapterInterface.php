<?php

namespace Janfish\Database\Criteria\Adapter;

interface AdapterInterface
{


    public function defineDoubleColumns(array $columns);

    public function defineIntColumns(array $columns);

    public function defineDateColumns(array $columns);

    public function defineFullTextColumns(array $columns);

    public function defineHideColumns(array $columns);

    public function defineTypeColumns(array $columns);

    public function setPagination($offset, $limit = null);

    public function count(): int;

    public function fetchOne(): array;

    public function fetchAll(): array;


}