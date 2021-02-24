# Articles API

## Project is using Symfony 5, PostgreSQL and Docker.

Launching first time:

```
composer install
docker-compose up -d
symfony console doctrine:migrations:migrate
symfony console doctrine:fixtures:load
symfony server:start -d
symfony open:local
```

Consecutive:

```
docker-compose start
symfony server:start -d
symfony open:local
```

### Endpoints:

-   `/api/articles`
    -   `GET`
    -   not secured
    -   query parameters: `page`
-   `/api/article/new`
    -   `POST`
    -   secured with Bearer token, provide `Authorization: "Bearer authtoken1337"` header
