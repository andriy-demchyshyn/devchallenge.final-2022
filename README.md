# DEV Challenge Final

## Stack
- PHP 8.1
- Laravel 9

## How to start service
1. Start Docker container: <code>docker-compose up -d</code>
2. SSH connect to Docker container: <code>docker exec -it devchallenge.final.app bash</code>
3. Install composer dependencies. Run in terminal: <code>composer install</code>
4. Run test in terminal: <code>php artisan test</code>

## API Endpoints
- <b>POST:</b> <code>http\://127.0.0.1:8080/api/image-input</code>\
  PHP Class: <code>app\Http\Controllers\ImageController::class</code>\
  Method: <code>process</code>
