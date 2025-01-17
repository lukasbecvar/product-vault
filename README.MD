# Product Vault
Open-source API system for managing product databases, designed with a focus on simplicity, flexibility, and security with efficient storage organization and manipulation of product data through a RESTful API, ensuring seamless integration with your e-shop or other applications that require product lists or product details.

## Features
* Product data management
* RESTful API (main interface for comunication with the system)
* Security user authentication and authorization system (Using JWT)
* CLI commands (for managing products)

## Installation
You can install the application on your server as a classic PHP application and manage dependencies via Composer, or you can use Docker.

## Environment configuration variables
| Variable | Description | Example value |
| --- | --- | --- |
| `APP_ENV` | Specific enviroment name | `dev` |
| `APP_SECRET` | Session & token encrypton key | `369af56dccfce490cb9325e8b4b59a90` |
| `API_TOKEN` | API access token for authentication | `1234` |
| `APP_VERSION` | App version identifier | `1.0` |
| `TRUSTED_HOSTS` | Trusted domain names | `^.*$` |
| `ALLOWED_IP_ADDRESSES` | Allowed ip addresses (use % for all ip-addresses) | `%` |
| `SSL_ONLY` | Enable only SSL traffic (true/false) | `false` |
| `MAINTENANCE_MODE` | Enable maintenance mode (true/false) | `false` |
| `LIMIT_CONTENT_PER_PAGE` | Pagination config (int value) | `10` |
| `REGISTRATION_WITH_API_ENDPOINT_ENABLED` | Enable registration API endpoint (true/false) | `true` |
| `DATABASE_LOGGING` | Log manager config | `true` |
| `LOG_LEVEL` | Log level (1: CRITICAL, 2: WARNING, 3: NOTICE, 4: INFO) | `4` |
| `EXCHANGE_RATE_API_ENDPOINT` | Currency convertor API config | `https://open.er-api.com/v6/latest` |
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
| `JWT_PASSPHRASE` | JWT passphrase | `f82fdd5f4644df4ba8fe9d600c5026abc7d7e97cb4413607073d9fc03aa85ba4` |
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
* PHPUnit
    * [Github](https://github.com/sebastianbergmann/phpunit)
* Better PHPUnit CLI output
    * [Github](https://github.com/robiningelbrecht/phpunit-pretty-print)
* PHPStan
    * [Github](https://github.com/phpstan/phpstan)

## TODO LIST
- [ ] Cache system
    - [ ] Cache product by id when product is requested
    - [ ] Get product by id from cache or mysql database if not in cache
    - [ ] Invalidate cached product if product is edited
    - [ ] Cache products list by specific filter (cache per page list)
    - [ ] Cache preload (CLI command)
        - [ ] Preload cache for all products
        - [ ] Preload cache for most used products list filtering
        - [ ] When preload process is not finished use mysql database for getting products
- [ ] Products export
    - [ ] Export products to excel file
    - [ ] Export products to pdf file 
    - [ ] Export products to xml file
    - [ ] Export products to json file
- [ ] Product data translation
    - [ ] Product name
    - [ ] Product description
    - [ ] Category name
    - [ ] Attribute name
- [ ] Access control system
    - [ ] Access log entity (id, access-type, time, ip-address, user-agent request-uri)
    - [ ] Access log method in log manager (log access to product by id or access to product list)
    - [ ] Controller for get access log data per concrete period (hour, day, week, month, year, alltime)
- [ ] Create API documentation with postman
    - [ ] Create postman collection
        - [ ] Create tests in postman
- [ ] Kubernetes deployment template
- [ ] React admin app implementation

## License
This software is licensed under the [MIT license](https://github.com/lukasbecvar/product-vault/blob/main/LICENSE).
