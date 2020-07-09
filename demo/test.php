<?php
include_once '../vendor/autoload.php';


use Janfish\Database\Criteria\Finder as Finder;

$di = new  Phalcon\Di\FactoryDefault();
$di->set('mongo', function () {
    return new \MongoDB\Client("mongodb://root:root@192.168.10.34/");
});

$di->setShared('db', function () {
    return new \Phalcon\Db\Adapter\Pdo\Mysql([
//        'host' => '192.168.10.13',
        'host' => 'mysql',
        'username' => 'root',
        'password' => 'root',
        'dbname' => 'car_insurance_genius_v2',
        'charset' => 'utf8',
        'port' => '3306',
        'options' => [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
        ],
    ]);
});


$find = new Finder(Finder::MONGO_MODE);
//$find = new Finder(Finder::MYSQL_MODE);
$find->setAliasDirectives([
    Finder::EQUAL_DIRECTIVE => '$eq'
]);
$find->setSchema('insurance');
$find->setTable('orderDraft');
$find->defineFullTextColumns(['licensePlateNo', 'engineNo', 'vin', 'accountNo']);
$find->defineDateColumns(['createdAt', 'updatedAt', 'quotedAt', 'paidAt', 'insuredAt', 'startAt', 'endAt']);
$find->setSort(['id' => 'ASC']);
$find->setColumns(['adminId', '_id', 'createdAt']);
//$find->defineHideColumns(['_id']);
$find->setPagination(0, 100);
$searchConditions = [
//    'id'=>1,
//    'adminId'=>1222,
//    'createdAt'=>["2017-12-04 16:50:40","2020-07-10"],
//    'type'=>["TCI","VCI"],
//    'companyId'=>["eq"=>1],
//    'source'=>["in"=>['PingAn']],

//
//    'licensePlateNo'=>"232",
//    'col21'=>["notIn"=>['2']],
//    'col3'=>["neq"=>'2'],
//    'col4'=>["gte"=>'2'],
//    'col5'=>["lte"=>'2'],
//    'col6'=>["neq"=>'sd'],
//    'col7'=>["gt"=>'2'],
//    'col8'=>["lt"=>'2'],
];
//$searchConditions=[];
//$searchConditions = [
//    'createdAt'=>[
//        "2020-07-08",
//        "2020-07-10"
//    ],
//    'col3'=>2,
//    'col2'=>["in"=>['2']],
//'col1'=>["eq"=>8],
//    'col0'=>["a","b"],
//];
//$searchConditions=[
//    'licensePlateNo'=>['regex'=>"jiasdiasdji"],
//    'engineNo'=>'2'
//];
$find->setConditions($searchConditions);
print_r($find->fetchOne());
print_r($find->fetchAll());
print_r($find->count());
//$result = [
//    'list' => $find->execute(),
//    'count' => $find->count(),
//];