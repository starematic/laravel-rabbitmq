<?php

namespace Starematic\RabbitMQ\Console;

use Exception;
use Illuminate\Console\Command;
use Starematic\RabbitMQ\Services\MessageConsumer;
use Illuminate\Support\Facades\Log;

class ConsumeQueueCommand extends Command
{
    protected $signature = 'rabbitmq:consume {queue}';
    protected $description = 'Consume messages from a RabbitMQ queue';

    /**
     * @throws Exception
     */
    public function handle(MessageConsumer $consumer): void
    {
        $queue = $this->argument('queue');
        $this->info("Listening to queue: $queue");

        $consumer->consume($queue, function ($payload) {
            Log::info("Consumed: ", $payload);
        });
    }
}
