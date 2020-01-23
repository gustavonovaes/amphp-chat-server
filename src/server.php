#!/usr/bin/env php
<?php

require __DIR__ . "/../vendor/autoload.php";

$settings = require __DIR__ . "/../config/settings.php";
require __DIR__ . "/functions.php";

use App\ChatServer;
use function App\log;

Amp\Loop::run(function () use ($settings) {
  ['host' => $host, 'port' => $port] = $settings;

  $server = new ChatServer("tcp://{$host}:{$port}");
  $server->listen();

  log("Listening on {$host}:{$port} ...");
});
