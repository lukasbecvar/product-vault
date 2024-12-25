# Product Vault
An open-source platform API system for managing product databases, designed with a focus on simplicity, flexibility, and security. The system enables efficient storage organization and manipulation of product data through a RESTful API, ensuring seamless integration with your e-shop or other applications that require product lists or product details. This system is based on [api-base](https://github.com/lukasbecvar/api-base)

## Product manager
- [X] Create product (parameters: name, description, price, price-currency (default: EUR))
- [X] Edit product
    - [X] Edit product name
    - [X] Edit product description
    - [X] Edit product price
    - [X] Edit product price-currency
    - [X] Deactivate product
    - [X] Activate product
    - [X] Disable non-active products in get methods
- [X] Delete product
- [ ] Assing category to product
- [ ] Remove category from product
- [ ] Assing attribute to product
- [ ] Remove attribute from product
- [ ] Remove all product attributes and categories when product is deleted
- [ ] Add product icon to product (use product asset manager)
- [ ] Change product icon (use product asset manager)
- [ ] Add product images to product (use product asset manager)
- [ ] Remove product image (use product asset manager)
- [ ] Delete all product assets, attributes and categories when product is deleted if not used by other products
- [ ] Get products
    - [ ] Get product by id - get product details by id
    - [ ] Get products by category - get products list by category
    - [ ] Get products by attributes - get products list by attributes (one or multiple attributes)
    - [ ] Get products list by search - get all products with matched search criteria (name, description, category)
    - [ ] All get methods pagination parameter - get products list with pagination (page parameter is optional)
    - [ ] All get methods sorting parameter - get products list with sorting (by: name, price, added-time) (sort parameter is optional)

## Product manager controllers backlog
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

## Product data translation backlog
- [ ] Product data translation
    - [ ] Product name
    - [ ] Product description
    - [ ] Category name
    - [ ] Attribute name

## Products export backlog
- [ ] Products export
    - [ ] Export products to excel file
    - [ ] Export products to pdf file 
    - [ ] Export products to xml file
    - [ ] Export products to json file

## Access control system backlog
- [ ] Access control system
    - [ ] Access log entity (id, access-type, time, ip-address, user-agent request-uri)
    - [ ] Access log method in log manager (log access to product by id or access to product list)
    - [ ] Controller for get access log data per concrete period (hour, day, week, month, year, alltime)

## Cache system backlog
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

## Code base backlog
- [ ] Create postman collection
- [ ] Create API documentation with postman
    - [ ] Create tests in postman
- [ ] Simple app info in README.MD
- [ ] Env config documentation in README.MD
- [ ] Complete app refactoring
- [ ] Release product-vault api docker image to github packages

## Frontend app backlog
- [ ] React app implementation
    - [ ] Login to app
    - [ ] Logout from app
    - [ ] Register new user
    - [ ] User system
        - [ ] Self update password (change password)
        - [ ] Admin panel
            - [ ] User list
            - [ ] Update user status
            - [ ] Update user roles
            - [ ] Delete user
    - [ ] Implement product dashboard with sorting and filtering
    - [ ] Implement product search
    - [ ] Implement producit detail view
    - [ ] Implement product create
    - [ ] Implement product edit
    - [ ] Implement product delete
    - [ ] Implement access log graph view (use access log data from api (access log entity))

## License
This software is licensed under the [MIT license](https://github.com/lukasbecvar/product-vault/blob/main/LICENSE).
