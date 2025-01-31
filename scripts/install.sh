#!/bin/bash

# install dependencies
if [ ! -d 'vendor/' ]
then
    docker-compose run composer
fi

# generate jwk key
if [ ! -d 'config/jwt/' ]
then
    docker-compose run php ./bin/console lexik:jwt:generate-keypair
fi
