#!/bin/bash

# create storage directory
if [ ! -d 'storage' ]
then
    mkdir ./storage
    mkdir ./storage/icons
    mkdir ./storage/images
fi

# set storage dir permissions
sudo chmod -R 777 ./storage
sudo chmod -R 777 ./var
