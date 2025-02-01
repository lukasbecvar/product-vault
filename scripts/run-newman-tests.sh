#!/bin/bash

# run postman tests with newman in docker container
docker run --network="host" -v $(pwd):/etc/newman -t postman/newman run postman-collection.json -e postman-environment.json
