<?php

namespace Starematic\RabbitMQ\Console;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Starematic\RabbitMQ\Contracts\QueueHandler;
use Starematic\RabbitMQ\Services\MessageConsumer;
use Throwable;

class ConsumeQueueCommand extends Command
{
    protected $signature = 'rabbitmq:consume {--queue=}';

    protected $description = 'Consume messages from a RabbitMQ queue';

    /**
     * @throws Exception
     */
    public function handle(MessageConsumer $consumer): void
    {
        $handlers = app()->tagged(QueueHandler::class);

        if (empty($handlers)) {
            $this->components->error('No queue handlers found. Make sure your handlers are tagged and implement the QueueHandler interface.');

            return;
        }

        $queueFilter = $this->option('queue');
        $filtered = collect($handlers);

        if ($queueFilter) {
            $filterList = array_map('trim', explode(',', $queueFilter));
            $filtered = $filtered->filter(fn ($h) => in_array($h->queue(), $filterList, true));
        }

        if ($filtered->isEmpty()) {
            $this->components->error('No handlers matched the provided --queue option.');

            return;
        }

        $queues = $filtered->map(fn ($handler) => $handler->queue())->unique()->values()->all();

        $this->newLine();
        $this->components->info('RabbitMQ consumer ready');
        $this->components->twoColumnDetail('Queues', '<fg=cyan>'.implode('</>, <fg=cyan>', $queues).'</>');
        $this->components->twoColumnDetail('Started at', now()->toDateTimeString());
        $this->newLine();

        foreach ($filtered as $handler) {
            $consumer->consume($handler->queue(), function ($payload) use ($handler) {
                $this->components->task("[{$handler->queue()}] Message received", function () use ($handler, $payload) {
                    try {
                        $handler->handle($payload);

                        return true;
                    } catch (Throwable $e) {
                        Log::error($e);

                        return false;
                    }
                });
            }, false);
        }

        $consumer->waitLoop();
    }
}
