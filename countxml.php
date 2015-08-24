<?php
require_once('./kafka.php');
require_once('./comm_producer.php');

try {
    runProducer();
} catch (Exception $e) {
    echo ('producer run error!'),PHP_EOF;
}


function runProducer(){
    //读取FTP的下载的xml源文件列表
    $kmlPath = '/home/webdata/xml';
    
    $xml_file = getFileList($kmlPath);
    if(empty($xml_file)){
        echo (date('Y-m-d h:i:m')."XML source files downloaded from the FTP is empty." ),PHP_EOF;
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


        echo ($i.'>>>'.$f.',file count:'.$fNum.',total:'.$totalNum)."/n";

        //备份文件:
//        backFile($f);
    }
    echo ('Total time:'.getTime($startTime)).'/n';
    
}

