#!/bin/bash
NUM=`ps -ef | grep $1`
PHP_NUM=`expr $NUM - 1`
if [${PHP_NUM} -lt 1];then
    `/usr/bin/php /usr/share/nginx/html/consumer.php $1`
fi
