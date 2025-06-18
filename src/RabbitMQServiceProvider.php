<?php

namespace LaravelRabbitMQ;

use Illuminate\Support\ServiceProvider;
use LaravelRabbitMQ\Services\RabbitMQPublisher;

class RabbitMQServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/rabbitmq.php', 'rabbitmq');

        $this->app->singleton(RabbitMQPublisher::class, function () {
            return new RabbitMQPublisher(
                config('rabbitmq.host'),
                config('rabbitmq.port'),
                config('rabbitmq.user'),
                config('rabbitmq.password')
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/config/rabbitmq.php' => config_path('rabbitmq.php'),
        ], 'rabbitmq-config');
    }
}
