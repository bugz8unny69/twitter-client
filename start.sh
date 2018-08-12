#!/bin/bash

export QUEUE=now,high,med,low,last
export APP_INCLUDE=main.php 
export PRE_START=1
export PREFIX=twitterclient
#export INTERVAL=20

if [ "$1" == 'queue_alone' ]; then
    export VVERBOSE=1
    php vendor/chrisboulton/php-resque/resque.php
else
    echo 'here';
    #php resque &> /dev/null & disown
    #php main.php
fi