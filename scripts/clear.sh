#!/bin/bash

# delete docker services data
sudo rm -rf .docker/services/

# delete jwt key
sudo rm -rf ./config/jwt

# delete UI assets
sudo rm -rf public/bundles/

# delete symfony cache folder
sudo rm -rf var/

# delete composer packages
sudo rm -rf vendor/
sudo rm -rf composer.lock

# delete resources storage
sudo rm -rf storage/
