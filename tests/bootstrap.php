<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/vendor/autoload.php';

define('TEST_QUEUE_NAME', 'delay_server_queue' . mt_rand(10000, 99999));