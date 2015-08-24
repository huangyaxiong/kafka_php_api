<?php

//数据库连接信息
$dsn="mysql:host=127.0.0.1;dbname=kml";
$user='root';
$pwd='';
$table='kml';

//kafka配置信息
$kafkaUrl='';
$kafkaPort='9092';
$kafkaProject='kmlroject_test';
$kafkaTopic='kmlroject_test-1,kmlroject_test-2';
$kafkaKey='kmlKey_test';
$kafkaGroup='kmlGroup_test';


//可卖量更新失败缓存
$kmlCacheFile='./cache/kafkaData.txt';
$kmlCacheBak='./cacheBak';

//可卖量更新接口
$kmlUpdateApi='';

$lockFile='./lock';

$lastId=0;
