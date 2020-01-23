<?php

error_reporting(E_ALL);
ini_set('display_errors', '0');

// Throws exceptions on errors
set_error_handler(function ($severity, $message, $file, $line) {
  if (error_reporting() & $severity) {
    throw new \ErrorException($message, 0, $severity, $file, $line);
  }
});

// Timezone
date_default_timezone_set($_SERVER['TIMEZONE'] ?? 'UTC');

return $settings = [
  'env' => null,
  'host' => $_SERVER['URI'] ?? '0.0.0.0',
  'port' => $_SERVER['PORT'] ?? 5000,
];
