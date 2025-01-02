#!/bin/bash

# clear console history
clear

# run tests process
docker-compose run --no-deps php bash -c "
    php vendor/bin/phpcbf &&
    php vendor/bin/phpcs &&
    php vendor/bin/phpstan analyze &&
    php bin/console doctrine:database:drop --env=test --if-exists --force &&
    php bin/console doctrine:database:create --if-not-exists --env=test &&
    php bin/console doctrine:migrations:migrate --no-interaction --env=test &&
    php bin/console doctrine:fixtures:load --no-interaction --env=test &&
    php bin/phpunit 2>/dev/null
"
