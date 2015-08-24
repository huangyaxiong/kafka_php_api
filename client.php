<?php
require_once('./kafka.php');
//$client = kafka::getInstance();
//$client->send();
/*for($i=1;$i<100;$i++){
   kafka::getInstance()->send('storeId_1','aa'.$i);
}
**/
$res = kafka::getInstance()->get('storeId_1');
print_r($res);
