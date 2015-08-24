<?php

function paseXml($xml){
    $content = file_get_contents($xml);
    if(!empty($content)){
        $xml = simplexml_load_file($xml);
        $result = object2Array($xml);
    }else{
        $result = '';
    }
    return $result;
}

function isFormat($data,$f){
	$res = true;
    $f = basename($f);
    if(empty($data)){
	$res = false;
        logs(date('H:i:s',time())." the file:  $f  is empty.");
      
    }

    if(empty($data['MenDian'])){
	$res = false;
        logs(date('H:i:s',time())." the file:'  $f   have not MenDian info.");
    }

    if(empty($data['MenDian']['ITEM'])){
	$res = false;
        logs(date('H:i:s',time())." the file:'  $f   have not Item info.");
       
    }
    backFile($f);
}

function formatKmlData($data,$f){

    $storeArr = $data['MenDian'];
    //单门店
    if(!empty($storeArr['DianHao'])){
        if(!empty($storeArr['ITEM']['RTHuoHao'])){//单item
            $kmldata[] = kmlData( $data['MenDian']['DianHao'],$storeArr['ITEM']['FenPei'],$storeArr['ITEM']['RTHuoHao'],$f,$data['DocHead']['DocDate']);
        }else{//多item
            foreach ($storeArr['ITEM'] as $item){
                if(is_array($item)){
                    $kmldata[] = kmlData( $data['MenDian']['DianHao'],$item['FenPei'],$item['RTHuoHao'],$f,$data['DocHead']['DocDate']);
                }
            }
        }
    }

    //多门店
    if(empty($storeArr['DianHao'])){
        foreach ($data['MenDian'] as $val) {
            if(count($val['ITEM']) == 1){//单item
                $kmldata[] = kmlData(  $val['DianHao'],$val['ITEM']['FenPei'],$val['ITEM']['RTHuoHao'],$f,$data['DocHead']['DocDate']);
            }else{//多item
               if(!empty($val['ITEM'])){
                foreach ($val['ITEM'] as $item) {
                    if (is_array($item)) {
                        $kmldata[] = kmlData(  $val['DianHao'], $item['FenPei'], $item['RTHuoHao'], $f, $data['DocHead']['DocDate']);
                    }
                }
               }
            }
        }
    }

    return $kmldata;
}

function kmlData($dianhao,$fenpei,$rthuohao,$file,$docdate,$kmlstatus = '_'){

    $ar_temp_data['storeId'] = $dianhao;
    $ar_temp_data['distribution'] = $fenpei;
    $ar_temp_data['rtNum'] = $rthuohao;
    $ar_temp_data['kmlStatus'] = '_';
    $ar_temp_data['kmlPath'] = $file;
    if($docdate){
        $ar_time = explode('/', $docdate);
        $ar_temp_data['docTime'] = $ar_time['0'].'-'.$ar_time['1'].'-'.$ar_time['2'].' '.$ar_time['3'].':'.$ar_time['4'].':'.$ar_time['5'];
    }
    $ar_temp_data['createTime'] = date('Y-m-d H:i:s');
    return $ar_temp_data;
}

//移动备份已经解析过的数据
function backFile($f){

    $fileArr = explode('/',$f);
    $filename = $fileArr[count($fileArr)-1];
    //$path = getconfig('kmlBakPath').'/'.date('Y-m-d'));
    //mkFolder($path);
    $path = getconfig('kmlBakPath');
    if(file_exists($f)){
 //       moveFile($f, $path.'/'.$filename);
$kmlBakPath='/home/webdata/htdocs/data/feiniu/snd/NEW_KML_BACKUP/'.date('Y-m-d');
       mkFolder($kmlBakPath);
       moveFile($f,$kmlBakPath.'/'.$filename);
       logs("success move to ".$kmlBakPath);
    }
    logs("success move to bakkup");
}
//转换成接口的参数格式
function formatKml( $kmldata,$f ){
   $topicArr = explode(',',getconfig('kafkaTopic'));
   $hash = new Flexihash();
   $hash->addTargets($topicArr);
    $msgs = [];
    if(!empty($kmldata)){
    foreach($kmldata as $k => $kml){

        $msg =  array(
            'DIANHAO' => $kml['storeId'],
            'FENPEI' => $kml['distribution'],
            'RTHUOHAO' => $kml['rtNum'],
            'CREATE_TIME' => $kml['createTime'],
            'DOC_TIME' =>$kml['docTime'],
            'KML_FILE' => $f
        );
        $key = $hash->lookup($kml['storeId'].$kml['rtNum']);
        $msgs[$key][] = $msg;
    }

    return $msgs;
    }
}

function insertKafka( $kmldata,$f ){
    $formatArr = formatKml($kmldata,$f);
    if(!empty($formatArr)){
      foreach($formatArr as $k => $list){
        $rows = array_chunk($list,50);
        foreach($rows as $row){
           kafka::getInstance()->sendList($k,$row);
        }
      }
    }
}
