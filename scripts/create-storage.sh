#!/bin/bash

# create storage directory
if [ ! -d 'storage' ]
then
    mkdir ./storage
fi

# set storage dir permissions
sudo chmod -R 777 ./storage
sudo chmod -R 777 ./var
