#!/bin/bash  
ProcNumber=`ps -ef |grep producer|grep -v grep|wc -l`  
if [ $ProcNumber -le 2 ];then  
    echo 'delete'
    cd /usr/share/nginx/html/lock/
    rm producer
    touch producer
    chmod 777 producer
fi  

ProcNumber=`ps -ef |grep consumer|grep -v grep|wc -l`
if [ $ProcNumber -le 2 ];then
    echo 'delete'
    cd /usr/share/nginx/html/lock/
    rm consumer
    touch consumer
    chmod 777 consumer
fi
