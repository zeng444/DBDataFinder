<?php
include_once '../vendor/autoload.php';

use Janfish\Database\Criteria\Finder as Finder;

$find = new Finder(Finder::MYSQL_MODE);
$find->defineFullTextColumns(['licensePlateNo', 'engineNo', 'vin', 'accountNo']);
$find->defineDateColumns(['createdAt', 'updatedAt', 'quotedAt', 'paidAt', 'insuredAt', 'startAt', 'endAt']);
$find->setSort(['id' => 'ASC']);
$find->setSchema('test');
$find->setColumns([]);
$find->setTable('order_basic');
$find->setPagination(0, 100);
$searchConditions = [
    'id'=>1,
    'name2'=>["a","b"],
    'name'=>["in"=>['2']],
    'age'=>["neq"=>['2']],
    'age2'=>["gte"=>['2']],
];
$find->setConditions($searchConditions);
$result = [
    'list' => $find->execute(),
    'count' => $find->count(),
];