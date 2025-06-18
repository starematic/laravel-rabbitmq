<?php

namespace Starematic\RabbitMQ\Services;

use Exception;
use PhpAmqpLib\Channel\AbstractChannel;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class MessageConsumer
{
    protected AMQPStreamConnection $connection;
    protected AbstractChannel|AMQPChannel $channel;

    /**
     * @throws Exception
     */
    public function __construct(string $host, int $port, string $user, string $password)
    {
        $this->connection = new AMQPStreamConnection($host, $port, $user, $password);
        $this->channel = $this->connection->channel();
    }

    /**
     * @throws Exception
     */
    public function consume(string $queue, callable $callback, bool $wait = true): void
    {
        $this->channel->queue_declare($queue, false, true, false, false);

        $this->channel->basic_consume($queue, '', false, true, false, false, function ($msg) use ($callback) {
            $payload = json_decode($msg->body, true, 512, JSON_THROW_ON_ERROR);
            $callback($payload);
        });

        if ($wait) {
            while ($this->channel->is_consuming()) {
                $this->channel->wait();
            }

            $this->channel->close();
            $this->connection->close();
        }
    }

    /**
     * @throws Exception
     */
    public function waitLoop(): void
    {
        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }

        $this->channel->close();
        $this->connection->close();
    }
}
