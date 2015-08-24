<?php
//日志文件
CONST LOG_FILE_PATH = './logs';

function getFileList( $dir ){
    if(!file_exists($dir)){
       mkdir($dir,0777);
    }
    $fileArray = '';
    if (false != ($handle = opendir ( $dir ))) {
        $i = 0;
        while ( false !== ($file = readdir ( $handle )) ) {
            $i++;
            if ($file != "." && $file != "..") {
                $arrs = explode('.',$file);
                $filetime = substr($arrs[0],7,8);
                if($arrs[1] == 'xml' || $arrs[1] == 'XML' && ($filetime == date('Ymd') || $filetime == date('Ymd')-1)){
                   $fileArray[$i]= $dir.'/'.$file;
                }
            }
        }
        closedir ( $handle );
    }
    return $fileArray;
}

function logs($msg, $type =1, $file = 'producer', $topic = 'topic'){
    $str = $topic == 'topic' ? '' : $topic.'-';
    $base = LOG_FILE_PATH.'/'.$file;
    if($type == 1){
        if($file == 'consumer'){
           $bath = $base.'/'.date('Y-m-d');           
           mkFolder($bath);
           $fileName = $bath.'/'.$topic;
        }else{
           $fileName = $base.'/run/'.date('Y-m-d');
        }
        file_put_contents($fileName,$msg." \n",FILE_APPEND);
    }else{
        $bath = $base.'/error/';        
        $fileName = $bath.'/'.$str.date('Y-m-d');;
        file_put_contents($fileName,$msg." \n",FILE_APPEND);
    }

}

function object2Array($stdclassobject) {
    $_array = is_object($stdclassobject) ? get_object_vars($stdclassobject) : $stdclassobject;
    foreach ($_array as $key => $value) {
        $value = (is_array($value) || is_object($value)) ? object2Array($value) : $value;
        $array[$key] = $value;
    }
    return $array;
}



function moveFile($sourcePath, $toPath){
   if(file_exists($sourcePath)){ 
       copy($sourcePath,$toPath);
       @unlink($sourcePath);
    }
}

function getconfig($ini,$file = './config.php',  $type="string")
{
    if ($type=="int")
    {
        $str = file_get_contents($file);
        $config = preg_match("/" . $ini . "=(.*);/", $str, $res);
        Return $res[1];
    }
    else
    {
        $str = file_get_contents($file);
        $config = preg_match("/" . $ini . "=\"(.*)\";/", $str, $res);
        if(@$res[1]==null)
        {
            $config = @preg_match("/" . $ini . "='(.*)';/", $str, $res);
        }
        Return  @$res[1];
    }
}

function updateconfig($ini, $value, $type="string", $file = 'config.php')
{
    $str = file_get_contents($file);
    $str2="";
    if($type=="int")
    {
        $str2 = preg_replace("/" . $ini . "=(.*);/", $ini . "=" . $value . ";", $str);
    }
    else
    {
        $str2 = preg_replace("/" . $ini . "=(.*);/", $ini . "=\"" . $value . "\";",$str);
    }
    file_put_contents($file, $str2);
}

function curlPost($uri,$data){
    $ch = curl_init ();
    curl_setopt ( $ch, CURLOPT_URL, $uri );
    curl_setopt ( $ch, CURLOPT_POST, 1 );
    curl_setopt ( $ch, CURLOPT_HEADER, 0 );
    curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($ch, CURLOPT_TIMEOUT,60);  
    curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
    $returnData = curl_exec ( $ch );
    curl_close ( $ch );
    return $returnData;
}


function getTime($starttime){
    $endtime = explode(' ',microtime());
    $thistime = $endtime[0]+$endtime[1]-($starttime[0]+$starttime[1]);
    return round($thistime,3);
}

function mkFolder($Folder){

  if(!is_readable($Folder)){

    mkFolder( dirname($Folder) );

    if(!is_file($Folder)) mkdir($Folder,0777);

    }

}

function getBackName(){
    $arrA = explode(" ",microtime());
    $arrB = explode('.',$arrA[0]);
    return $arrA[1].$arrB[1];
}

function lock($type = 1){
   $file = $type == 1 ? 'producer' : 'consumer';
   file_put_contents('./lock/'.$file,1);
}

function lockConsumer($type){
   file_put_contents('./lock/'.$type,1);
}

function unlockConsumer($type){
  
   file_put_contents('./lock/'.$type,0);
}


function unlock($type = 1){
    $file = $type == 1 ? 'producer' : 'consumer';
    file_put_contents('./lock/'.$file,'');
}

function checkLock($type = 1){
   $file = $type == 1 ? 'producer' : 'consumer';
   $lock = file_get_contents('./lock/'.$file);
   return $lock;
}
