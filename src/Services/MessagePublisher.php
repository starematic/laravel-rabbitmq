<?php

namespace Starematic\RabbitMQ\Services;

use Exception;
use JsonException;
use PhpAmqpLib\Channel\AbstractChannel;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class MessagePublisher
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
     * @throws JsonException
     */
    public function publish(string $queue, array $payload): void
    {
        $this->channel->queue_declare($queue, false, true, false, false);

        $message = new AMQPMessage(json_encode($payload, JSON_THROW_ON_ERROR), [
            'content_type' => 'application/json',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ]);

        $this->channel->basic_publish($message, '', $queue);
    }

    /**
     * @throws Exception
     */
    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }
}
