# Product Vault
Open-source API system for managing product databases, designed with a focus on simplicity, flexibility, and security with efficient storage organization and manipulation of product data through a RESTful API, ensuring seamless integration with your e-shop or other applications that require product lists or product details. This system is based on [api-base](https://github.com/lukasbecvar/api-base)

## Product manager controllers
- [ ] Edit product controller
    - [ ] Rename product
    - [ ] Update price
    - [ ] Update product description
    - [ ] Add new attribute to product
    - [ ] Update product attribute values
    - [ ] Delete product attribute
    - [ ] Add new category to product
    - [ ] Remove category from product
    - [ ] Disable product visibility (edit is-active)
- [ ] Delete product controller (delete product by id)

## Product data translation
- [ ] Product data translation
    - [ ] Product name
    - [ ] Product description
    - [ ] Category name
    - [ ] Attribute name

## Products export
- [ ] Products export
    - [ ] Export products to excel file
    - [ ] Export products to pdf file 
    - [ ] Export products to xml file
    - [ ] Export products to json file

## Access control system
- [ ] Access control system
    - [ ] Access log entity (id, access-type, time, ip-address, user-agent request-uri)
    - [ ] Access log method in log manager (log access to product by id or access to product list)
    - [ ] Controller for get access log data per concrete period (hour, day, week, month, year, alltime)

## Cache system
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

## Others
- [ ] Create postman collection
- [ ] Create API documentation with postman
    - [ ] Create tests in postman
- [ ] Simple app info in README.MD
- [ ] Env config documentation in README.MD
- [ ] Complete app refactoring
- [ ] Release product-vault api docker image to github packages

## Frontend app
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
