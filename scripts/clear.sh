#!/bin/bash

# delete docker services data
sudo rm -rf .docker/services/

# delete UI assets
sudo rm -rf public/bundles/

# delete symfony cache folder
sudo rm -rf var/

# delete composer packages
sudo rm -rf vendor/
sudo rm -rf composer.lock
