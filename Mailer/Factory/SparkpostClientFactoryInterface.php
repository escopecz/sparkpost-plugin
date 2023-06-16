<?php

declare(strict_types=1);

namespace MauticPlugin\SparkpostBundle\Mailer\Factory;

use SparkPost\SparkPost;

interface SparkpostClientFactoryInterface
{
    public function create(string $host, string $apiKey, int $port = null): SparkPost;
}