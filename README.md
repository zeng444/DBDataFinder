## JANFISH DATA FINDER

####  MAIN FEATURE

```
$finderer = new Finder(Finder::MONGO_MODE);
//$finder = new Finder(Finder::MYSQL_MODE);\
$finder->setConnection((Di::getDefault())->get('mongo'));
$finder->setAliasDirectives([
    Finder::EQUAL_DIRECTIVE => '='
]);
$finder->setSchema('insurance');
$finder->setTable('orderDraft');
$finder->defineAliasColumns([
    'id'=>'nid'
]);
$finder->defineFullTextColumns(['queryValue', 'engineNo', 'vin', 'accountNo']);
$finder->defineDateColumns(['createdAt', 'updatedAt', 'quotedAt', 'paidAt', 'insuredAt', 'startAt', 'endAt']);
$finder->setSort(['id' => 'ASC']);
$finder->setColumns(['adminId', '_id', 'createdAt','queryValue']);
//$finder->defineHideColumns(['_id']);
$finder->setPagination(0, 100);
$finder->setConditions([

     //SEARCH "=" OPERATION
    'id' => 1,
    'companyId' => ['eq' => 1],

    //SEARCH "!=" OPERATION
    'col3' => ["neq" => '2'],

    //SEARCH ">=" AND "<=" FOR DATATIME OPERATION
    'createdAt' => ["2017-12-04 16:50:40", "2020-07-10"],
    '$where' => 'successOrderTotal + failOrderTotal > orderTotal',

    //SEARCH "IN" OPERATION
    'type' => ["TCI", "VCI"],
    'source' => ["in" => ['PingAn']],

    // SEARCH "NOT IN" OPERATION
    'col21' => ["notIn" => ['2']],

    //SEARCH "LIKE" OPERATION
    'queryValue' => ["regex" => "0008x"],
    'licensePlateNo' => "232",

    //SEARCH ">="  OPERATION
    'col4' => ["gte" => '2'],

    //SEARCH "<="  OPERATION
    'col5' => ["lte" => '2'],

    //SEARCH ">"  OPERATION
    'col7' => ["gt" => '2'],

    // SEARCH "<"  OPERATION
    'col8' => ["lt" => '2'],

]);
print_r($finder->debug());
print_r($finder->fetchOne());
//print_r($find->fetchAll());
//print_r($find->count());
```

#### Condition Directive Alias

 | DIRECTIVES     |ALIAS    |  
 |-----|------|
| regex | $regex |
| eq | $eq |
| neq | $ne |
| in | $in | 
| notIn | $nin | 
| gt | $gt | 
| lt | $lt | 
| get | $gte | 
| lte | $lte | 
| where | $where | 

