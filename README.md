# Requiments and details

- PHP 8.1+
- Database: (mysql or sqlite)
- Laravel 13 [requiments](https://laravel.com/docs/13.x/deployment)
- No UI, only API (no authentification)
- Postman API collection [here](lp-app.postman_collection.json)
- PHP PCOV or xdebug to run tests coverage

# Setup

- Create .env file

    ```
    cp .env.example .env
    ```

- Install framework libraries

    ```
    composer install
    ```

- Create app key and fill out database connection to the .env file

    ```
    php artisan key:generate
    ```

- Run migrations and seed

    ```
    php artisan migrate:fresh --seed
    ```

- Default seeder will create test data:
    - 5 warehouses
    - 25 products with random stock (between 10 and 100) in each warehouse;

# API

### Create order (reserve stock) `/api/orders`

<details>  
    <summary><code>POST</code> <code><b>/api/orders</b></code> <code>(create order - reserve stock)</code></summary>

```json
{
    "items": [
        {
            "id": 1, // product id
            "quantity": 100
        },
        {
            "id": 2,
            "quantity": 5
        },
        {
            "id": 3,
            "quantity": 2
        }
    ]
}
```

</details>

### Ship order (consume stock) `/api/orders/ship`

<details>  
    <summary><code>POST</code> <code><b>/api/orders/ship</b></code> <code>(ship order - consume stock)</code></summary>

```json
{
    "id": 1 // order id
}
```

</details>

### Cancel order (cancel stock & rebalance) `/api/orders/{orderId}`

<details>  
    <summary><code>DELETE</code> <code><b>/api/orders/{orderId}</b></code> <code>(release stock and rebalance)</code></summary>

```json
    // no body
```

</details>

### Product list `/api/products/`

<details>  
    <summary><code>GET</code> <code><b>/api/orders/ship</b></code> <code>(get list of products)</code></summary>

```json
  // no body
```

</details>

### Product `/api/products/{productId}`

<details>
    <summary><code>GET</code> <code><b>/api/orders/ship</b></code> <code>(get product details)</code></summary>

```json
  // no body
```

</details>

# Tests

- used PHPUnit
- Included coverage report in `./build` folder (generated with PCOV)
- To run tests use command: `php artisan test`
- To build coverage report: `php artisan test --coverage-html build` requires **PHP PCOV or xdebug**
