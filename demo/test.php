<?php
include_once '../vendor/autoload.php';

//github token 222fcb4d956fa47229dc9fa346d8d42f68b75d37
use Janfish\Database\Criteria\Finder as Finder;
use Phalcon\Di;

$di = new  Phalcon\Di\FactoryDefault();
//$di->set('mongo', function () {
//    return new \MongoDB\Client("mongodb://root:root@192.168.10.34/");
//});

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

$searchConditions = [
    'id' => 11,
    //    'adminId' => 1222,
    //    'createdAt' => ["2017-12-04 16:50:40", "2020-07-10"],
    //    'type' => ["TCI", "VCI"],
    //    'where' => 'this.adminId > 0',
    //    'companyId' => ['eq' => 1],
    //    'source' => ["in" => ['PingAn']],
    //    'queryValue' => "602",
    //    'queryValue' => ["regex" => "0008x"],
    //    'licensePlateNo' => "232",
    //    'col21' => ["notIn" => ['2']],
    //    'col3' => ["neq" => '2'],
    //    'col4' => ["gte" => '2'],
    //    'col5' => ["lte" => '2'],
    //    'col6' => ["neq" => 'sd'],
    //    'col7' => ["gt" => '2'],
    //    'col8' => ["lt" => '2'],
];
$finder = new Finder(Finder::MYSQL_MODE,true);
//$finder = new Finder(Finder::MYSQL_MODE);
//$finder->setConnection((Di::getDefault())->get('mongo'));
//$finder->setAliasDirectives([
//    Finder::EQUAL_DIRECTIVE => '$eqdd'
//]);
$finder->defineAliasColumns([
    'id'=>'nid'
])
->setSchema('car_insurance_genius_v2')
->setTable('insurance_order')
->defineFullTextColumns(['queryValue', 'engineNo', 'vin', 'accountNo'])
->defineDateColumns(['createdAt', 'updatedAt', 'quotedAt', 'paidAt', 'insuredAt', 'startAt', 'endAt'])
->setSort(['nid' => 'ASC'])
->setColumns(['companyId', 'nid', 'createdAt','orderFrom', 'type'])
->defineHideColumns(['orderFrom'])
->setPagination(0, 1)
->setConditions($searchConditions);
//print_r($finder->debug());
$finder->fetchAll();
print_r($finder->fetchOne());
print_r($finder->count());