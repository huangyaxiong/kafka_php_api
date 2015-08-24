<?php
require_once('./kafka.php');
require_once('./flexihash.php');
require_once('./comm_producer.php');

$lockfile = '/tmp/producer.lock';

if(file_exists($lockfile)){
	$c_time=filectime($lockfile);
	$e_time=time()-$c_time;
	if($e_time>8*60){
		unlink($lockfile);
	}else{
		exit();
	}
}
file_put_contents($lockfile, 1, true);

try {
    /*$lock = checkLock();
    if($lock){exit;} 
    lock(); */
    runProducer();
} catch (Exception $e) {
    logs('producer run error!');
    unlock();
}

function runProducer(){
    $kmlPath = getconfig('kmlPath');
    
    $xml_file = getFileList($kmlPath);
    $lockfile = '/tmp/producer.lock';
    if(empty($xml_file)){
        logs(date('Y-m-d h:i:m')."XML source files downloaded from the FTP is empty." );
        unlink($lockfile); 
        exit;
    }

    sort($xml_file);
    $startTime = explode(' ',microtime());
    $totalNum = 0;
    $i = $n = 1;
    foreach($xml_file as $f){

        //解析文件生成数组
        $data = paseXml($f);

        //XML格式检查
        $res = isFormat($data,$f);
        if($res === false){
		continue;
        }
        //格式化
        $kmldata = formatKmlData($data, $f);

        $i++;
        $fNum = count($kmldata);
        $totalNum += $fNum;

        //入队列
        $kafkaTime = explode(' ',microtime());
        $fileName = basename($f);
        insertKafka($kmldata,$fileName);

        logs($i.'>>>'.basename($f).',file count:'.$fNum.',total:'.$totalNum.',into kafka time:'.getTime($kafkaTime));
        if($n > 100){
            usleep(200);
            $n = 1;
        }

        //备份文件:
        backFile($f);
    }
    logs('Total time:'.getTime($startTime));
   //unlock(); 
   //$lockfile = '/tmp/producer.lock';
   unlink($lockfile); 
   exit;
}

