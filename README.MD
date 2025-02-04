# Product Vault
Open-source API system for managing product databases, designed with a focus on simplicity, flexibility, and security with efficient storage organization and manipulation of product data through a RESTful API, ensuring seamless integration with your e-shop or other applications that require product lists or product details.

## Index
* [Features](#features)
* [API documentation](#api-documentation)
* [Installation](#installation)
* [File system](#file-system)
* [Database](#database)
* [CLI commands](#cli-commands)
* [Environment configuration variables](#environment-configuration-variables)
* [Dependencies](#dependencies--requirements)
* [License](#license)

## Features
* Product data management
* RESTful API (main interface for communication with the system)
* Security user authentication and authorization system (Using JWT)
* CLI commands (for managing products)

# API documentation
##### Nelmio UI
The documentation for API endpoints can be found at the route `/api/doc`, with Nelmio UI complete documentation and also allows testing requests directly in the web browser. Alternatively, you can find documentation in JSON format at `/api/doc.json` (You can use this JSON response to import the configuration into Postman).

## Installation
##### Docker compose
Example of stack deployment ([docker-compose-stack-configuration](.github/examples/docker-compose-example.yml)).

## File system
##### Internal symfony cache
The internal Symfony cache is stored in the project root in directory ``/var/www/var``. You don't need to worry about this, but you can mount this cache if needed.
##### Products assets storage
The products assets storage is stored in the project root in directory ``/var/www/storage``. If you run the application in a container, you need to create a storage volume and mount it to this directory to ensure data persistence.

## Database
##### MySQL - long term storage
MySQL database is used for products data storage, with access configured through environment variables. It stores product data, administrator user accounts, and some logs.
##### Redis - cache storage
Redis is used for caching data and store invalidated user tokens and some runtime data.

## CLI commands
The application is primarily designed as a REST API but also includes console commands for managing products, logs, and users. These commands allow easy automation of various processes, for example, using cron jobs. This eliminates the need to send cURL requests, as actions can be executed directly via the Symfony console.

## Environment configuration variables
| Variable | Description | Example value |
| --- | --- | --- |
| `APP_ENV` | Specific environment name | `dev` |
| `APP_SECRET` | Session & token encryption key | `369af56dccfce490cb9325e8b4b59a90` |
| `API_TOKEN` | API access token for authentication | `1234` |
| `APP_VERSION` | App version identifier | `1.0` |
| `TRUSTED_HOSTS` | Trusted domain names | `^.*$` |
| `ALLOWED_IP_ADDRESSES` | Allowed ip addresses (use % for all IP addresses) | `%` |
| `SSL_ONLY` | Enable only SSL traffic (true/false) | `false` |
| `MAINTENANCE_MODE` | Enable maintenance mode (true/false) | `false` |
| `LIMIT_CONTENT_PER_PAGE` | Pagination config (int value) | `10` |
| `REGISTRATION_WITH_API_ENDPOINT_ENABLED` | Enable registration API endpoint (true/false) | `true` |
| `DATABASE_LOGGING` | Log manager config | `true` |
| `LOG_LEVEL` | Log level (1: CRITICAL, 2: WARNING, 3: NOTICE, 4: INFO) | `4` |
| `EXCHANGE_RATE_API_ENDPOINT` | Currency converter API config | `https://open.er-api.com/v6/latest` |
| `CACHE_PRODUCT_DATA` | Enable caching for product data | `true` |
| `PRODUCT_CACHE_TTL` | Cache expiration for product data | `43200` (12 hours) |
| `EXCHANGE_DATA_CACHE_TTL` | Currency convertor API config | `86400` (1 day cache expiration) |
| `DATABASE_DRIVER` | Database driver | `pdo_mysql` |
| `DATABASE_HOST` | Database host | `127.0.0.1` |
| `DATABASE_PORT` | Database port | `3306` |
| `DATABASE_NAME` | Database name | `product_vault` |
| `DATABASE_USERNAME` | Database username | `root` |
| `DATABASE_PASSWORD` | Database password | `root` |
| `REDIS_SCHEME` | Redis scheme | `redis` |
| `REDIS_HOST` | Redis host | `127.0.0.1` |
| `REDIS_PORT` | Redis port | `6379` |
| `REDIS_USER` | Redis user | `default` |
| `REDIS_PASSWORD` | Redis password | `redis_test_password` |
| `JWT_TOKEN_TTL` | JWT token TTL (in seconds) | `2629536` (1 month token expiration) |
| `JWT_SECRET_KEY` | JWT secret key | `%kernel.project_dir%/config/jwt/private.pem` |
| `JWT_PUBLIC_KEY` | JWT public key | `%kernel.project_dir%/config/jwt/public.pem` |
| `JWT_PASSPHRASE` | JWT passphrase | `f82fdd5f4644df4ba8fe9df82fdd5f4644df4ba8fe9d` |
| `MAILER_ENABLED` | Enable mailer | `false` |
| `MAILER_HOST` | Mailer host | `smtp.seznam.cz` |
| `MAILER_PORT` | Mailer port | `465` |
| `MAILER_USERNAME` | Mailer username | `service@becvar.xyz` |
| `MAILER_PASSWORD` | Mailer password | `password` |

## Dependencies & requirements
Based on [api-base](https://github.com/lukasbecvar/api-base)
* PHP 8.3
    * [Website](https://php.net)
* Redis
    * [Website](https://redis.io)
* MySQL
    * [Website](https://www.mysql.com)
* Symfony framework
    * [Website](https://symfony.com)
* Doctrine ORM
    * [Github](https://github.com/doctrine/orm)
* Lexik JWT Authentication Bundle
    * [Github](https://github.com/lexik/LexikJWTAuthenticationBundle)
* PHPSpreadsheet
    * [Github](https://github.com/PHPOffice/PhpSpreadsheet)
* PHPUnit
    * [Github](https://github.com/sebastianbergmann/phpunit)
* Better PHPUnit CLI output
    * [Github](https://github.com/robiningelbrecht/phpunit-pretty-print)
* PHPStan
    * [Github](https://github.com/phpstan/phpstan)

## License
This software is licensed under the [MIT license](LICENSE).
