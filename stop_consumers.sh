#!/usr/bin/env bash

PID_PATH="/tmp"

consumers=(
    'status'
    'multiplex'
    'transcode'
    'trim'
)

signal=9

if [ ! -z "$1" ]
then
    signal=$1
fi

for consumer in ${consumers[@]}
do
    pid_file="${PID_PATH}/${consumer}_consumer.pid"

    kill -s $signal `cat "${pid_file}"`
done
