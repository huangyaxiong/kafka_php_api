<?php
ini_set('display_errors','on');
include '../config.php';
//echo $kmlPath.'/a.txt';
//echo file_get_contents($kmlPath.'/a.txt');
//echo file_get_contents('./aa.txt');a
//file_put_contents('aa.txt','aaaa>>',FILE_APPEND);
//打开目录  
$p = '/home/webdata/htdocs/data/feiniu/snd/KML_BACKUP';
//$kmlPath = './source';
$dir = @ dir($p);  
$i=0;
//列出目录中的文件  
while (($file = $dir->read())!==false){
   $arr = [];
   $arr = explode('.',$file);
   $i++;
   if($arr[1] == 'XML'){
       echo $i.'>>>'.$kmlBakPath.$file." >> ".$kmlPath."\n";
        copy($p.'/'.$file,'../xml/'.$file);
        if($i>=10)break;
   }
}  
$dir->close();  
  
