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

PID1_FILE=${SYMFONY_DIR}/terptube_consumer1.pid
PID2_FILE=${SYMFONY_DIR}/terptube_consumer2.pid
startAudioConsumer()
{
	
	php "${SYMFONY_DIR}/app/console" rabbitmq:consumer upload_audio &
	process1=$!
	echo $process1>$PID1_FILE
}

startVideoConsumer()
{
	php "${SYMFONY_DIR}/app/console" rabbitmq:consumer transcode &
	process2=$!
	echo $process2>$PID2_FILE
}

if [ -d $SYMFONY_DIR ]
then
#NEED TO ADD LOGGIN TO THE LOG_DAEMON
#	if [ -f $PID1_FILE ] || [ -L $PID1_FILE ]
#	then
#		read PID1 <$PID1_FILE
#		if ! ps -p $PID1 >/dev/null
#		then
#			startAudioConsumer
#		fi
#	else
#		startAudioConsumer
#	fi
	if [ -f $PID2_FILE ] || [ -L $PID2_FILE ]
	then
		read PID2 <$PID2_FILE
		if ! ps -p $PID2 >/dev/null
		then
			startVideoConsumer
		fi
	else
		startVideoConsumer
	fi

else
	echo "$SYMFONY_DIR is not a valid directory"
	exit 1
fi
