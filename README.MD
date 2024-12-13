# Product Vault
An open-source platform API system for managing product databases, designed with a focus on simplicity, flexibility, and security. The system enables efficient storage organization and manipulation of product data through a RESTful API, ensuring seamless integration with your e-shop or other applications that require product lists or product details.

## register request
curl -X POST http://localhost/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "firstName": "John",
    "lastName": "Doe",
    "password": "securePassword123"
  }'

## login request
curl -X POST http://localhost/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@test.test",
    "password": "test"
  }'

## logout request
curl -X POST http://localhost/api/auth/logout \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer <token>"

## Backlog
## Stage 1 - API base
- [X] Main index controller
- [X] Error controller
- [X] Exception handling event subscriber
- [X] Log system
    - [X] Create log entity (id, message, level, time, user-id, ip-address, user-agent, request-uri, request-method, status)
    - [X] Log manager
        - [X] Save log
        - [X] Get logs by status
        - [X] Get logs by user-id
        - [X] Get logs by ip-address
        - [X] Set all logs to readed
        - [X] Truncate logs table
        - [X] All logs get methods with pagination and sorting
    - [X] CLI command to get logs
    - [X] CLI command to set all logs to readed
    - [X] CLI command to truncate logs
- [X] App middleware
    - [X] Database online check middleware
    - [X] Escape request data middleware
    - [X] Maintenance mode middleware
    - [X] Security check middleware
        - [X] SSL check
        - [X] Allow connection for specific ip-address or for all ip-addresses
- [X] Protect exception error message in production mode
- [X] User system (jwt auth)
    - [X] Create user entity (id, email, firstname, lastname, roles, password, register-time, last-login, ip-address, user-agent)
    - [X] Create user manager service
    - [X] User registration command
    - [X] Update user role command (add, remove parameter)
    - [X] Delete user command
    - [X] Update user status command
    - [X] Reset user password command
    - [X] User registration (api endpoint)
    - [X] User login JWT (with symfony security bundle and nelmio jwt)
        - [X] Implement password hash to register user and user fixture
	- [X] Log user login event and update user data on login
    - [X] Check if user status is active in login process (check if user is not blocked)
    - [X] Get logs by user email instead of user id in log reader cli command
    - [X] User logout invalidate JWT token (api endpoint)
        - [X] JWT token blacklist (redis)
- [ ] User info contoller
    - [ ] User info endpoint
        - [ ] Get firstname
        - [ ] Get lastname
        - [ ] Get email
        - [ ] Get roles
        - [ ] Get is-active
        - [ ] Get last-login
        - [ ] Get register-time
- [ ] Allow get access token from http only cookie
- [ ] Get user data CLI command
- [ ] Get user id in save log method 
- [ ] Setup nelmio api doc
- [ ] Setup CSRF protection
- [ ] Revise log levels in save log method
- [ ] Email manager
    - [ ] Send email - password reset
    - [ ] Send email - user status change
    - [ ] Send email - user registration
- [ ] Complete base refactoring
- [ ] Base documentation
- [ ] Release app base to separate repo (api-base)

## Stage 2 - Product Management
- [ ] Create product schema
    - [ ] Create product entity (id, name, description, price (default EUR), added-time, last-edit-time, is-active)
    - [ ] Create product category entity (id, name)
    - [ ] Create product category value entity (id, product-id, category-id)
    - [ ] Create product attribute entity (id, name)
    - [ ] Create product attribute value entity (id, product-id, attribute-id, attribute-value)
    - [ ] Create product icon entity (id, product-id, icon-file) (one icon per product)
    - [ ] Create product image entity (id, product-id, image-file) (multiple images per product)
    - [ ] Create storage folder for product icons and images
- [ ] Product manager
    - [ ] Create product - create new product
    - [ ] Edit product - edit product by id
    - [ ] Delete product - delete product by id
    - [ ] Create new attribute - create new attribute
    - [ ] Add attribute to product - add new attribute to product by id (add one attribute or multiple attributes)
    - [ ] Delete attribute from product - delete attribute from product by id (delete one attribute or multiple attributes)
    - [ ] Get product by id - get product details by id
    - [ ] Get products by category - get products list by category
    - [ ] Get products by attributes - get products list by attributes (one or multiple attributes)
    - [ ] Get products list by search - get all products with matched search criteria (name, description, category)
    - [ ] All get methods pagination parameter - get products list with pagination (page parameter is optional)
    - [ ] All get methods sorting parameter - get products list with sorting (by: name, price, added-time) (sort parameter is optional)
- [ ] Create product controller
    - [ ] Create product with attributes
        - [ ] name is required
        - [ ] description is optional
        - [ ] price is required
        - [ ] category is required
        - [ ] attributes is optional
        - [ ] icon is required
        - [ ] images is optional
- [ ] Edit product controller
    - [ ] Rename product
    - [ ] Update price
    - [ ] Update product description
    - [ ] Update product attributes
    - [ ] Disable product visibility (edit is-active)
    - [ ] Edit product icon
    - [ ] Edit product images
- [ ] Delete product controller
    - [ ] Delete product
    - [ ] Delete product attributes and attribute values of deleted product (if attribute is not used by other products)
- [ ] Get product controller (allow access with api token from .env)
    - [ ] Get all products
    - [ ] Get product by id
    - [ ] Get all products by category
    - [ ] Get products by attribute or multiple attributes
    - [ ] Get product by name (search by name and description pattern match)
    - [ ] Get only new products (by time period: last hour, last day, last week, last month, last year) order by newest first
    - [ ] Sort by name, price, added-time in all get methods
    - [ ] Option to set price currency (for convert currency form default currency (EUR) to required format)
    - [ ] Pagination for products result
        - [ ] Limit items per page
        - [ ] Endpoint for getting pages count
        - [ ] Implement pagination info to all get results
            - [ ] First page
            - [ ] Last page
            - [ ] Next page
            - [ ] Previous page
            - [ ] Current page
            - [ ] Total pages
            - [ ] Total items
            - [ ] Items per page
- [ ] Get product assets controller
    - [ ] Get product icon by id
    - [ ] Get list product images by id
    - [ ] Get product image by product-id and image-file-name
- [ ] Product data translation
    - [ ] Product name
    - [ ] Product description
    - [ ] Category name
    - [ ] Attribute name
- [ ] Products export
    - [ ] Export products to excel file
    - [ ] Export products to pdf file 
    - [ ] Export products to xml file
    - [ ] Export products to json file
- [ ] Access control system
    - [ ] Access log entity (id, access-type, time, ip-address, user-agent request-uri)
    - [ ] Access log method in log manager (log access to product by id or access to product list)
    - [ ] Controller for get access log data per concrete period (hour, day, week, month, year, alltime)
- [ ] Cache system
    - [ ] Cache product by id when product is requested
    - [ ] Get product by id from cache or mysql database if not in cache
    - [ ] Invalidate cached product if product is edited or deleted
    - [ ] Cache products icon and images with apache mod_expires
    - [ ] Cache products list for specific filter (cache per page list)
    - [ ] Invalidate lists cache when product is edited, deleted or added new
    - [ ] Cache preload
        - [ ] Preload cache for all products
        - [ ] Preload cache for most used products list filtering
        - [ ] When preload process is not finished use mysql database for getting products
- [ ] Create postman collection
- [ ] Create API documentation with postman
- [ ] Simple app info in README.MD
- [ ] Env config documentation in README.MD
- [ ] Complete app refactoring
- [ ] Release product-vault api docker image to github packages

## Stage 3 - Frontend app
- [ ] React app implementation
    - [ ] Login to app
        - [ ] Store JWT token in http-only cookie
    - [ ] Logout from app
    - [ ] Register new user
    - [ ] Implement product dashboard with sorting and filtering
    - [ ] Implement product search
    - [ ] Implement producit detail view
    - [ ] Implement product create
    - [ ] Implement product edit
    - [ ] Implement product delete
    - [ ] Implement access log graph view (use access log data from api (access log entity))

## License
This software is licensed under the [MIT license](https://github.com/lukasbecvar/product-vault/blob/main/LICENSE).
