## Requirements

* PHP >= 8.1
* Redis (for queue)
* any database: mysql, pgsql, sqlite

## Instruction deploy

use docker
[docker-compose-laravel](https://github.com/aschmelyun/docker-compose-laravel)

or local

```
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
```

## Run workers (jobs)

[app/Jobs/TransactionProcessJob.php](https://github.com/yuriy-vasilev/user-balance/blob/main/app/Jobs/TransactionProcessJob.php)

``php artisan queue:work`` (one process)

how to start several processes watch here
[Supervisor Configuration](https://laravel.com/docs/10.x/queues#supervisor-configuration)

## Structure payload

Example add message to queue: 
```
POST /
Content-Type: application/json

{
    "action": "add",
    "sender": 1,
    "recipient": null,
    "identifier": "example123",
    "amount": 10.50
}
```
Action can be one of values: 
* **add**
* **subtract**
* **transfer** (recipient required)
* **freeze** 
* **approve** (available for frozen transaction)
* **reject** (available for frozen transaction)
