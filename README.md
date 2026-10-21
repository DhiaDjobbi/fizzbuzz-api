# FizzBuzz REST API

A parameterised FizzBuzz generator exposed as a REST API, with a statistics endpoint that tracks the most frequent request. Built as a technical assessment for a Senior PHP Developer position.

![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php&logoColor=white) ![Symfony](https://img.shields.io/badge/Symfony-8.1-000000?logo=symfony&logoColor=white) ![MySQL](https://img.shields.io/badge/MySQL-8.4-4479A1?logo=mysql&logoColor=white) ![Docker](https://img.shields.io/badge/Docker-✓-2496ED?logo=docker&logoColor=white)

---

## Quick Start

You only need [Docker](https://docs.docker.com/get-docker/) & Docker Compose installed.

```bash
git clone https://github.com/DhiaDjobbi/fizzbuzz-api.git
cd fizzbuzz-api

cp .env.example .env        # edit passwords / secrets as needed

docker compose up -d
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
```

The API is now running at **http://localhost:8000**.
phpMyAdmin is available at **http://localhost:8081** for inspecting the database.

---

## API

### `GET /fizzbuzz`

Returns a FizzBuzz sequence based on the provided parameters.

| Parameter | Type    | Constraints                |
|-----------|---------|----------------------------|
| `int1`    | integer | required, positive         |
| `int2`    | integer | required, positive         |
| `limit`   | integer | required, between 1–10 000 |
| `str1`    | string  | required, max 50 chars     |
| `str2`    | string  | required, max 50 chars     |

```bash
curl "http://localhost:8000/fizzbuzz?int1=3&int2=5&limit=15&str1=fizz&str2=buzz"
```

```json
["1","2","fizz","4","buzz","fizz","7","8","fizz","buzz","11","fizz","13","14","fizzbuzz"]
```

### `GET /stats`

Returns the most frequently used parameter combination and its hit count. Returns `404` if no requests have been recorded yet.

```bash
curl http://localhost:8000/stats
```

```json
{
  "int1": 3,
  "int2": 5,
  "limit": 15,
  "str1": "fizz",
  "str2": "buzz",
  "hits": 12
}
```

### Swagger UI (dev only)

Interactive API documentation is available at **[http://localhost:8000/api/doc](http://localhost:8000/api/doc)** via [NelmioApiDocBundle](https://github.com/nelmio/NelmioApiDocBundle).

---

## Project Structure

```
src/
├── Controller/
│   ├── FizzBuzzController.php       # GET /fizzbuzz — delegates to services
│   └── StatsController.php          # GET /stats
├── Dto/
│   └── FizzBuzzRequest.php          # Validated via #[MapQueryString] + Symfony Validator
├── Entity/
│   └── FizzBuzzRequest.php          # Doctrine entity (unique constraint on the 5 params)
├── Repository/
│   └── FizzBuzzRequestRepository.php
├── Service/
│   ├── FizzBuzzGenerator.php        # Pure FizzBuzz logic
│   └── FizzBuzzRequestTracker.php   # Persists / increments hit counts
├── EventListener/
│   ├── HttpErrorListener.php              # Consistent JSON error responses
│   ├── StrictQueryParameterListener.php   # Rejects unknown query parameters
│   └── ValidationErrorListener.php        # Human-readable validation messages
└── EventSubscriber/
    ├── RateLimitSubscriber.php       # Per-IP rate limiting
    └── SecurityHeadersSubscriber.php # Hardened response headers
```

### Key Choices

- **Controllers stay thin.** They receive the request, call a service, return a response. The actual FizzBuzz logic and the hit-tracking persistence each live in their own service.
- **`#[MapQueryString]` for request binding.** The DTO is validated automatically by Symfony's Validator on the way in — no manual parsing needed.
- **Strict query parameters.** A custom listener rejects any unknown query parameter with a `400`, so typos like `?limt=10` don't silently pass through.
- **Rate limiting with `symfony/rate-limiter`.** A fixed-window policy (30 requests/minute per IP) is enforced via an event subscriber. The limiter is configured in `config/packages/rate_limiter.yaml` and wired through `#[Autowire]`, so adjusting the limits is just a YAML change.
- **Security headers.** Every response gets `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`, `Referrer-Policy: no-referrer`, and a restrictive `Permissions-Policy`. This is handled globally by `SecurityHeadersSubscriber`.
- **Migrations over `schema:update`.** Schema changes go through Doctrine Migrations so they're versioned and reproducible.

---

## Testing

![Tests](https://img.shields.io/badge/tests-30%20passed-brightgreen) ![Assertions](https://img.shields.io/badge/assertions-50-brightgreen) ![Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen)

| Metric    | Coverage          |
|-----------|-------------------|
| Classes   | 100.00% (11/11)   |
| Methods   | 100.00% (16/16)   |
| Lines     | 100.00% (129/129) |

The test suite is split into **functional tests** (full HTTP requests through `WebTestCase`) and **unit tests** for every service, listener, and subscriber.

```bash
docker compose exec -e APP_ENV=test -e XDEBUG_MODE=coverage app \
  php bin/phpunit --testdox --coverage-text --coverage-html var/coverage
```

The HTML coverage report is generated in `var/coverage/index.html`.

---

## Code Quality

| Tool             | Config                      | Command                                                    |
|------------------|-----------------------------|------------------------------------------------------------|
| PHP-CS-Fixer     | `.php-cs-fixer.dist.php`    | `docker compose exec app vendor/bin/php-cs-fixer fix`      |
| PHPStan (lvl 6)  | `phpstan.dist.neon`         | `docker compose exec app vendor/bin/phpstan analyse`       |

---

## Docker Stack

| Service        | Image            | Port |
|----------------|------------------|------|
| **app**        | PHP 8.4-cli      | 8000 |
| **mysql**      | MySQL 8.4        | 3306 |
| **phpmyadmin** | phpMyAdmin 5.2   | 8081 |
