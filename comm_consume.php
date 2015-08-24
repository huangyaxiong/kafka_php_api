<?php
//通过接口跟新可卖量
function updataKml($kmls, $starttime, $file='', $source=1, $topic){
    //访问API
    $kmlUpdateApi = getConfig('kmlUpdateApi');
    $unique = unique_arr($kmls);//去重
    //print_r($unique);exit;
    $soadata = formatApiData($unique);//去重
    
    $returnData = curlPost($kmlUpdateApi,array('data'=>$soadata));
    logs($returnData, 1, 'consumer',$topic);
    print_r($returnData);exit;
    //logs(' Access :'.$kmlUpdateApi, 1, 'consumer',$topic);
    //logs(' Params is:'.json_encode($data), 1, 'consumer',$topic);
    //logs(' Return is:'.$returnData, 1, 'consumer',$topic);    
    $cachePath = getconfig('kmlCachePath');
    $cacheBack = getconfig('kmlCacheBak');
    //验证还回结果
    $objs = json_decode($returnData);
    if(empty($returnData) || $objs->status != 0 || empty($objs->data)){
        if($source == 2){
           $dir = $cacheBack.'/'.date('Y-m-d').'/'.$topic;

           mkFolder($dir);
           $toPath = $dir.'/'.basename($file); 
           moveFile($file, $toPath);
                    
           logs(date('H:i:s').' API error:'.$objs->msg.'; file:'.$file.' has move to '.$toPath, 1, 'consumer',$topic);
        }else{
           if($objs->status < 3){
              $cacheTopicPath = $cachePath.'/'.$topic;
              mkFolder($cacheTopicPath);
              $filename = basename($file);
              $source = $cacheTopicPath.'/'.$filename;
              file_put_contents($source,$data);
              logs(date('H:i:s').' API error:'.$objs->msg.'; file:'.$file.' has backup to '.$source, 1, 'consumer',$topic);
           }else{
              $backFile = $cacheBack.'/'.basename($file);
              moveFile($file,$backFile);              
              logs(date('H:i:s').' API error:'.$objs->msg.'; file '.basename($file) .'has move to '.$backFile, 1, 'consumer', $topic);
           }
        }       
    }
    if($source == 2){
       if(file_exists($file))    unlink($file);
    }

    $arr = object2Array($objs);
    $returnKml = $arr['data'];
    
    $insertNum = 0;
    if($source == 1){
        $insertNum = addKml($returnKml,$kmls, $topic);
    }
    logs('kafka num:'.count($kmls).',send data num:'.count($unique).', API return:'.count($returnKml).',insertNum:'.$insertNum.',Time: '.getTime($starttime),1,'consumer', $topic);
    $kmls = '';
}


function addKml($returndata,$kmls,$topic){

    $result = formatKml($returndata,$kmls);
    $dsn = getconfig('dsn');
    $user = getconfig('user');
    $pwd = getconfig('pwd');
    $table = getconfig('table');
    $db = new PDO($dsn , $user, $pwd);

    $arr[] = '';
    $sql = "INSERT INTO $table (`storeId`,`distribution`,`rtNum`,`kmlStatus`,`itno`,`qty`,`docTime`,`createTime`,`updateTime`,`kmlPath`) VALUES ";
    foreach($result as $k => $arr){
        $sql .= '("'. $arr['storeId'].'","'.$arr['distribution'].'","'.$arr['rtNum'].'","'.$arr['kmlStatus'].'","'.$arr['itno'].'","'.$arr['qty'].'","'.$arr['docTime'].'","'.$arr['createTime'].'","'.date('Y-m-d H:i:s',time()).'","'.$arr['kmlPath'].'"),';
    }
   $sql = substr($sql,0,strlen($sql)-1);
 
    $row = $db->exec($sql);

    if(empty($lastId)){
        logs('error insert into, SQL:'.$sql,2,'consumer',$topic);
    }

    return $row;
}
//格式化调用API的接口
/*

prog(string):调用程序,
seq(string):批次号(文档名)
items:[{
itSern(string):厂商自用料号(RT货号),
storeNo(string):门店编号,
qty(number):库存分配量
}]

*/
function formatApiData($kmls){
    $result = [];
    $data = object2Array($kmls);
    if(!empty($data)){    
      foreach($data as $k => $da){
        $result[$k]['seq'] = $da['KML_FILE'];
        $result[$k]['itSern'] = $da['RTHUOHAO'];
        $result[$k]['storeNo'] = $da['DIANHAO'];
        $result[$k]['qty'] = $da['qty'];
      }
    }
    $soadata = [
        'prog'  => 'kml consumer',
        'items' =>  $result
    ];
    return json_encode($soadata);
}
//去重获得最新的kml
function unique_arr($array2D){
    $arr = $output = [];
    if(!empty($array2D)){

    foreach ($array2D as $obj){
        $arr[$obj->DIANHAO.$obj->RTHUOHAO] = (array)$obj;
    }

    return $arr;
    }
}

//合并队列和api还回的数组
function formatKml($res,$kmls){
    $rdata = [];
    $key = '';
    if(!empty($res)){
      foreach($res as $d){
        $storeId = $d['storeId'];
        $rtNum = $d['rtNum'];
        $key = $storeId.$rtNum;
        $itno = !empty($d['itno']) ? $d['itno'] : '';
        $qty = !empty($d['qty']) ? $d['qty'] : '';
        $rdata[$key] = $d['kmlStatus'].','.$itno.','.$qty;
      }
    }
    $data = $arr = [];
    $kmlarr = object2Array($kmls);
    ksort($kmlarr);
    foreach($kmlarr as $k=>$kml){
        $arr = explode(',',$rdata[$kml['DIANHAO'].$kml['RTHUOHAO']]);
        $data[$k]['storeId'] = $kml['DIANHAO'];
        $data[$k]['rtNum'] = $kml['RTHUOHAO'];
        $data[$k]['distribution'] = $kml['FENPEI'];
        $data[$k]['kmlStatus'] = $arr[0];
        $data[$k]['itno'] = $arr[1];
        $data[$k]['qty'] = $arr[2];
        $data[$k]['docTime'] = $kml['DOC_TIME'];
        $data[$k]['kmlPath'] = $kml['KML_FILE'];
        $data[$k]['createTime'] = $kml['CREATE_TIME'];
        $data[$k]['updateTime'] = date('Y-m-d H:i:s');
        $arr = [];
    }

    return $data;
}
