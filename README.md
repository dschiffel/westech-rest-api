# Product Management REST API

PHP REST API for product management, built without a framework using Symfony components and PostgreSQL.

## Requirements

* PHP 8.4+
* PostgreSQL 17+
* Composer
* Docker & Docker Compose (optional but recommended)

## Installation

### Using Docker (Recommended)

1. Clone the repository.
2. Run `make setup` to build and start the containers.
3. The API will be available at `http://localhost:8080`.

### Manual Installation

1. Clone the repository.
2. Run `composer install`.
3. Copy `.env.example` to `.env` and configure your database credentials.
4. Run `php -S localhost:8000 -t public`.
5. Run migrations by visiting `http://localhost:8000/?migrate=1`.

## Environment Variables

* `APP_ENV`: `dev` or `prod`
* `APP_DEBUG`: `true` or `false`
* `APP_BEARER_TOKEN`: Token for API authentication
* `DB_HOST`: Database host
* `DB_PORT`: Database port
* `DB_NAME`: Database name
* `DB_USER`: Database user
* `DB_PASSWORD`: Database password

## API Authentication

All `/products` endpoints require a Bearer token in the `Authorization` header.

Example:
`Authorization: Bearer westech-secret-token`

## API Endpoints

* `GET /health`: Health check (no auth required)
* `POST /products`: Create a product
* `PUT /products/{id}`: Update a product
* `DELETE /products/{id}`: Delete a product
* `GET /products?page=1&limit=20&category=electronics&brand=Apple`: List products with pagination and filtering
* `POST /products/test?source=local`: Create a test product from local data
* `POST /products/test?source=remote`: Create a test product from DummyJSON

## cURL Examples

### Health Check
```bash
curl http://localhost:8080/health
```

### Create Product
```bash
curl -X POST http://localhost:8080/products \
  -H "Authorization: Bearer westech-secret-token" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "iPhone 15",
    "description": "Latest Apple smartphone",
    "brand": "Apple",
    "category": "electronics",
    "price_without_vat": 999.00,
    "vat_rate": 20
  }'
```

### List Products
```bash
curl "http://localhost:8080/products?category=electronics" \
  -H "Authorization: Bearer westech-secret-token"
```

## Makefile Commands

* `make setup`: Initialize project (Docker)
* `make up`: Start containers
* `make down`: Stop containers
* `make migrate`: Run database migrations
* `make test`: Run PHPUnit tests
* `make shell`: Access app container shell
