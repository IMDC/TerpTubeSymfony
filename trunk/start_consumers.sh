#!/usr/bin/env bash

readonly SCRIPT_NAME=$(basename $0)

CURRENT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
if [ $# = 1 ]
then
    SYMFONY_DIR=$1
elif [ $# = 0 ]
then
    SYMFONY_DIR="${CURRENT_DIR}"
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

log() {
  echo "$@"
  logger -p user.notice -t $SCRIPT_NAME "$@"
}

err() {
  echo "$@" >&2
  logger -p user.error -t $SCRIPT_NAME "$@"
}

startConsumer() {
    /usr/bin/php "${SYMFONY_DIR}/app/console" rabbitmq:consumer "${1}" &
    pid=$!
    echo $pid > "${2}"
}

if [ -d $SYMFONY_DIR ]
then
#NEED TO ADD LOGGIN TO THE LOG_DAEMON

    for consumer in ${consumers[@]}
    do
        pid_file="${SYMFONY_DIR}/${consumer}_consumer.pid"

        if [ -f $pid_file ] || [ -L $pid_file ]
        then
	 #   log "PiD file $pid_file exists"
            read pid < $pid_file
            if /bin/ps -p $pid > /dev/null
            then
	#	log "Pid file $pid_file with $pid is running"
                continue
            fi
        fi
	log "starting consumer $consumer $pid_file"
        startConsumer $consumer $pid_file
    done

else
    echo "$SYMFONY_DIR is not a valid directory"
    exit 1
fi
