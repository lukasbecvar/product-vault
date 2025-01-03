# Product Vault
Open-source API system for managing product databases, designed with a focus on simplicity, flexibility, and security with efficient storage organization and manipulation of product data through a RESTful API, ensuring seamless integration with your e-shop or other applications that require product lists or product details.

## Backlog
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
- [ ] React admin app implementation

## License
This software is licensed under the [MIT license](https://github.com/lukasbecvar/product-vault/blob/main/LICENSE).
