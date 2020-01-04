<p align="center"><img src="https://res.cloudinary.com/loglia/image/upload/v1572656182/logo-dark_obmuma.svg"></p>
<p align="center"><i>Laravel Client</i></p>
<p align="center">
    <a href="https://github.com/loglia/laravel-client/actions"><img src="https://github.com/loglia/laravel-client/workflows/PHPUnit/badge.svg" alt="Build Status"></a>
    <a href="https://packagist.org/packages/loglia/laravel-client"><img src="https://poser.pugx.org/loglia/laravel-client/v/stable.svg" alt="Latest Stable Version"></a>
</p>

## About Loglia

[Loglia](https://www.loglia.app) is a cloud-based Laravel logging and monitoring solution. Loglia will take your Laravel application's logs and make them searchable, filterable and linkable to the rest of your team. If you've ever wondered what your app would say if it could speak, Loglia will tell you.

## System Requirements

The Loglia client requires the following be installed and available on your system:

- Laravel 5.4+
- PHP 7.0+
- cURL system library (available on the command line via `curl`)

## Quick start

**Firstly, require the package with Composer**:

```bash
composer require loglia/laravel-client
```
    
The package uses [package discovery](https://laravel.com/docs/master/packages#package-discovery) so you shouldn't need to add the service provider to `app.php`. If you've disabled package discovery in your app, or are using a Laravel version before 5.5, add `Loglia\LaravelClient\LaravelClientServiceProvider::class` to the `providers` array in `app.php` manually.

**Secondly, publish the Loglia configuration file**.

```bash
php artisan vendor:publish --tag=loglia
```
    
You need an API key in order to send your application's logs to Loglia. When creating an application in the Loglia UI, you will be given an API key. Copy it into your `.env` file:

```
LOGLIA_KEY=ICJCaskOl6YQAmKaXgVbpvD6o9BUA311
```

**Thirdly, configure your application to send its logs to Loglia**.

*Note: If you're using a Laravel version before 5.6, you don't need to do this. The package automatically configures your logs to send to Loglia.*

Crack open the `logging.php` config file and add this under the `channels` array:

```php
'loglia' => [
    'driver' => 'loglia'
],
```
    
Then change the `LOG_CHANNEL` environment variable in `.env` to use that channel.

```
LOG_CHANNEL=loglia
```

You logs will now be sent to the application you have set up in the Loglia dashboard!

## HTTP Logging

This package ships with a `LogHttp` middleware which can be used to log all of the HTTP requests sent to your application. This functionality ships as middleware so that you have complete control over which of your routes log requests.

If you would like to log all HTTP requests across your whole application, you can add the `LogHttp` middleware to your global middleware stack in `app/Http/Kernel.php`:

```php
protected $middleware = [
    // ... other middleware ...
    \Loglia\LaravelClient\Middleware\LogHttp::class,
];
```
    
Alternatively, you are free to assign the middleware a name in the `$routeMiddleware` array and assign it only to specific routes:

```php
protected $routeMiddleware = [
    // ... other middleware ...
    'log.http' => \Loglia\LaravelClient\Middleware\LogHttp::class,
];
```
    
And then use it as normal in your route definitions:

```php
Route::group(['middleware' => ['log.http']], function () {
    Route::get('/', function () {
        return view('welcome');
    });
});
```

## Configuration

The package ships with a `loglia.php` configuration file that can be used to tweak the Loglia-specific configuration. The comments in this file should be self-explanatory, but below is a reference.

### `api_key`

This is the API key that will authenticate your application with Loglia. Without it, Loglia won't know which application to relate your logd with. You should generate an API key in your application settings and set its value here.

### `http.header_blacklist`

When logging HTTP requests, Loglia will also capture the HTTP headers in the request and response. Some of these headers contain sensitive information such as credentials and cookies. This array contains a list of HTTP headers that should be scrubbed from the request and response before sending to Loglia. It is pre-filled with sensible defaults but you are free to add or remove any header you want.
