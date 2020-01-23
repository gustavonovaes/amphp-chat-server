<?php

$settings['env'] = 'development';

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Error Handler
(new \Whoops\Run)->pushHandler(new \Whoops\Handler\PlainTextHandler())->register();
