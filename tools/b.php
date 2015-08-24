<?php
ini_set('display_errors','on');
include 'comm_fun.php';
$dir = '/usr/share/nginx/html/cacheBak/'.date('Y-m-d');
if(false != ($handle = opendir ( $dir ))) {
        $i = 0;
	//$file = readdir ( $handle );var_dump($file);
        while ( false !== ($file = readdir ( $handle )) ) {
            $i++;
            if ($file != "." && $file != "..") {
               $fileArray[$i]= $dir.'/'.$file;
            }
        }
        closedir ( $handle );
    }
$n = 0;
foreach($fileArray as $a){
   $aa = json_decode(file_get_contents($a));
   //print_r($aa);
   $n += count($aa);
}
echo $n;
