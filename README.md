## Laravel API 

(Assuming you've installed Laravel & composer)

## Installation

Clone this project using below command or download as zip and install in your 
system.
```bash
Git Clone https://github.com/shafeek2112/laravel-api.git
```

After download the project, run composer install to install necessary packages.
```bash
composer install
```

Create the database and configure the DB connection in .env file. Open .env file to modify the DB credentials to suit your needs, 

```
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:S9Pgbu3/0ukDtLmrHiNyoSYaFIBv3MxE5MsW8L3esj8=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack

DB_CONNECTION=mysql
DB_HOST=192.168.64.2
DB_PORT=3306
DB_DATABASE=laravel_api
DB_USERNAME=shafeek
DB_PASSWORD=shafeek

BROADCAST_DRIVER=log
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=null
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=mt1

MIX_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
MIX_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

After setup your .env, download Laravel Passport using composer
```bash
composer require laravel/passport
```
Then run your migration

```bash
php artisan migrate
```

Then install passport service using below command 
```bash
php artisan passport:install
```

After install passport, then run DB seed command to seed the default admin user record into your DB for quick setup

```bash
php artisan db:seed
```

Now you can start the server by run 
```bash
php artisan serve
```

You can see your local server running on your system.

## API Info & Flow instruction

Please refer the user manual document to use API and to underestand the flow.


## Tests

Navigate to the project root and run `vendor/bin/phpunit` after installing all the composer dependencies and after the .env file was created.
