<?php

namespace Starematic\RabbitMQ;

use Illuminate\Support\ServiceProvider;
use Starematic\RabbitMQ\Services\MessagePublisher;
use Starematic\RabbitMQ\Services\MessageConsumer;

class RabbitMQServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/rabbitmq.php', 'rabbitmq');

        $this->app->singleton(MessagePublisher::class, function () {
            return new MessagePublisher(
                config('rabbitmq.host'),
                config('rabbitmq.port'),
                config('rabbitmq.user'),
                config('rabbitmq.password')
            );
        });

        $this->app->singleton(MessageConsumer::class, function () {
            return new MessageConsumer(
                config('rabbitmq.host'),
                config('rabbitmq.port'),
                config('rabbitmq.user'),
                config('rabbitmq.password')
            );
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/rabbitmq.php' => config_path('rabbitmq.php'),
        ], 'rabbitmq-config');
    }
}
