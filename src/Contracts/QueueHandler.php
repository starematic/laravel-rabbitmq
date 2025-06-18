<?php

namespace Starematic\RabbitMQ\Contracts;

interface QueueHandler
{
    public function queue(): string;

    public function handle(array $payload): void;
}
