#!/usr/bin/env bash

CURRENT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
if [ $# = 1 ]
then
    SYMFONY_DIR=$1
elif [ $# = 0 ]
then
    SYMFONY_DIR="${CURRENT_DIR}/../../../../"
else
    echo "Usage $0 [ SYMFONY_DIR ]"
    exit 1
fi

consumers=(
    'status'
    'multiplex'
    'transcode'
    'trim'
)

startConsumer() {
    php "${SYMFONY_DIR}/app/console" rabbitmq:consumer "${1}" &
    pid=$!
    echo $pid > "${2}"
}

if [ -d $SYMFONY_DIR ]
then
#NEED TO ADD LOGGIN TO THE LOG_DAEMON

    for consumer in ${consumers[@]}
    do
        pid_file="${consumer}_consumer.pid"

        if [ -f $pid_file ] || [ -L $pid_file ]
        then
            read pid < $pid_file
            if ps -p $pid > /dev/null
            then
                continue
            fi
        fi

        startConsumer $consumer $pid_file
    done

else
    echo "$SYMFONY_DIR is not a valid directory"
    exit 1
fi
