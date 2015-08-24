<?php
// $lockfile = '/tmp/mytest.lock';
 
// if(file_exists($lockfile)){
//     exit();
// }else{
//     file_put_contents($lockfile, 1, true);
// }

require_once('./kafka.php');
require_once('./comm_consume.php');
$topic = $argv[1];
try {
    runConsumer($topic);
    //lock($topic)
} catch (Exception $e) {
    logs('consume run error!',1,'consumer',$topic);
//    unlock(2);
}

function runConsumer($topic){
    $lockfile = '/tmp/mytest.lock';
    $startTime = explode(' ',microtime());
    $kmlCachePath = getconfig('kmlCachePath');
    //本地缓存里存在数据则优先执行
    $cacheFiles = getFileList('./cache/'.$topic);
    if(!empty($cacheFiles)){
        sort($cacheFiles);
        foreach($cacheFiles as $f){
	   $kmls = json_decode(file_get_contents($f)); 
           $items = array_chunk($kmls , 25);
           foreach($items as $item){
              updataKml($item,$startTime,$f,2,$topic);
           }
        }
    }
    
 //  $i = 1;
    $f = '';
    logs(date('h:i:s',time()).$topic.' start ...',1,'consumer',$topic);
    while($da = kafka::getInstance()->get($topic)){
       
         $starttime = explode(' ',microtime());
        
        if(!empty($da->messageList)){
            foreach($da->messageList as $d){
                $kmls[] = json_decode($d->message);
            }
            //$i++;
            //if($i > 10){
                updataKml($kmls,$starttime,$f,1,$topic);
                usleep(10);
                logs(date('H:i:s').'sleep 10', 1, 'consumer',$topic);
                $kmls = [];
            /*    $i = 1;
            }
        }else{
            if(!empty($kmls)){
               updataKml($kmls,$starttime,$f,1, $topic);
            }
            break;*/
        }else{
             unlink($lockfile);
             logs('success total time:'.getTime($startTime), 1, 'consumer',$topic);
             echo 'aa';
               exit;
        }

    }
    logs('success total time:'.getTime($startTime), 1, 'consumer',$topic);
    unlink($lockfile);

}
