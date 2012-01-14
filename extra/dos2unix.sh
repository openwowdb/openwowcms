#!/bin/sh

# STRIP carriage returns from all php files in a directory pointed by argument 1
# usage ./windows_to_dos directory


if test -z $1
then
  echo 'Usage: ' $0 ' directory'
  exit 1
fi

SOURCES=`ls -1 . |grep -e \.php$`


for s in $SOURCES
do
  echo 'Working on ' $s
  tr -d '\r' < $s  > z.php 
  mv z.php $s
done



