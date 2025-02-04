#!/bin/bash

# install app requirements
sh scripts/install.sh

# fix storage permissions
sh scripts/fix-storage-permissions.sh

# build and start docker services
docker-compose up --build
