<?php

namespace Starematic\RabbitMQ\Console;

use Exception;
use Illuminate\Console\Command;
use Starematic\RabbitMQ\Services\MessageConsumer;
use Illuminate\Support\Facades\Log;

class ConsumeQueueCommand extends Command
{
    protected $signature = 'rabbitmq:consume {--queue=}';
    protected $description = 'Consume messages from a RabbitMQ queue';

    /**
     * @throws Exception
     */
    public function handle(MessageConsumer $consumer): void
    {
        $queueArg = $this->option('queue');

        if (! $queueArg) {
            $this->error('You must specify at least one queue using --queue=');
            return;
        }

        $queues = array_map('trim', explode(',', $queueArg));

        foreach ($queues as $queue) {
            $this->info("Listening to queue: $queue");

            $consumer->consume($queue, function ($payload) use ($queue) {
                Log::info("[$queue] Consumed: ", $payload);
            }, false);
        }

        $consumer->waitLoop();
    }
}
