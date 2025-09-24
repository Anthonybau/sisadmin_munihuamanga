#!/bin/bash
apache=`ps awx | grep 'apache' |grep -v grep|wc -l`
if [ $apache == 0 ]; then
    service apache2 restart
    echo "Apache estaba caido y el cron lo reactivo."
fi


zend=`ps awx | grep 'zend-server' |grep -v grep|wc -l`
if [ $zend == 0 ]; then
    service zend-server restart
    echo "Zend-server estaba caido y el cron lo reactivo."
fi
