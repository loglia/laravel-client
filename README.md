<p align="center"><img src="https://res.cloudinary.com/loglia/image/upload/v1572656182/logo-dark_obmuma.svg"></p>
<p align="center"><i>Laravel Client</i></p>
<p align="center">
    <a href="https://travis-ci.org/loglia/laravel-client"><img src="https://travis-ci.org/loglia/laravel-client.svg" alt="Build Status"></a>
    <a href="https://packagist.org/packages/loglia/laravel-client"><img src="https://poser.pugx.org/loglia/laravel-client/v/stable.svg" alt="Latest Stable Version"></a>
</p>

## About Loglia

[Loglia](https://www.loglia.app) is a cloud-based Laravel logging and monitoring solution. Loglia will take your Laravel application's logs and make them searchable, filterable and linkable to the rest of your team. If you've ever wondered what your app would say if it could speak, Loglia will tell you.

## System Requirements

The Loglia client requires the following be installed and available on your system:

- Laravel 5.6+
- PHP 7.1+
- cURL system library (available on the command line via `curl`)

## Quick start

**Firstly, require the package with Composer**:

    composer require loglia/laravel-client
    
The package uses [package discovery](https://laravel.com/docs/5.6/packages#package-discovery) so you shouldn't need to add the service provider to `app.php`. If you've disabled package discovery in your app, add `Loglia\LaravelClient\LaravelClientServiceProvider::class` to the `providers` array in `app.php` manually.

**Secondly, publish the Loglia configuration file**.

    php artisan vendor:publish --tag=loglia
    
You need an API key in order to send your application's logs to Loglia. Once you've set up your application in the Loglia UI, go to application settings and copy the API key into your project's environment variables.

    LOGLIA_KEY=2jeTB67XHLNK4w5OH6NIZCN5OGDAQQ43c1pTo7XuTqwIHsZihfe4EGf8hH6Ufdtc

**Thirdly, configure your application to send its logs to Loglia**.

Crack open the `logging.php` config file and add this under the `channels` array:

    'loglia' => [
        'driver' => 'custom',
        'via' => Loglia\LaravelClient\LogliaLogger::class,
    ]
    
Then change the `LOG_CHANNEL` environment variable in `.env` to use that channel.

    LOG_CHANNEL=loglia
