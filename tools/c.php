<?php
echo time().'>>>'.microtime();
function getBackName(){
    $arrA = explode(" ",microtime());
    $arrB = explode('.',$arrA[0]);
    return $arrA[1].$arrB[1];
}
echo getBackName();
