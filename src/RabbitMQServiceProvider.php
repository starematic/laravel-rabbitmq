<?php

namespace Starematic\RabbitMQ;

use Illuminate\Support\ServiceProvider;
use Starematic\RabbitMQ\Console\ConsumeQueueCommand;
use Starematic\RabbitMQ\Services\MessageConsumer;
use Starematic\RabbitMQ\Services\MessagePublisher;

class RabbitMQServiceProvider extends ServiceProvider
{
    public function register(): void
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

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ConsumeQueueCommand::class,
            ]);
        }
        $this->publishes([
            __DIR__.'/Config/rabbitmq.php' => config_path('rabbitmq.php'),
        ], 'rabbitmq-config');
    }
}
