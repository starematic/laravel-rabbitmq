<?php

namespace Starematic\RabbitMQ\Console;

use Exception;
use Illuminate\Console\Command;
use Starematic\RabbitMQ\Services\MessageConsumer;

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
            $this->components->error('You must specify at least one queue using --queue=');
            return;
        }

        $queues = array_map('trim', explode(',', $queueArg));

        $this->newLine();
        $this->components->info("RabbitMQ Consumer Ready");
        $this->components->twoColumnDetail('Queues', '<fg=cyan>' . implode('</>, <fg=cyan>', $queues) . '</>');
        $this->components->twoColumnDetail('Started at', now()->toDateTimeString());
        $this->newLine();

        foreach ($queues as $queue) {
            $consumer->consume($queue, function ($payload) use ($queue) {
                $this->components->task("[$queue] Message received", function () {
                    return true;
                });
            }, false);
        }

        $consumer->waitLoop();
    }
}
