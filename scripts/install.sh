#!/bin/bash

# install dependencies
if [ ! -d 'vendor/' ]
then
    docker-compose run composer
fi

# fix storage permissions
sudo chmod -R 777 var/
sudo chown -R www-data:www-data var/
