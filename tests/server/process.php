<?php
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

$config = [
    'server_type' => 'process',
    'daemonize' => true,
    'process_name' => 'dq_process',
    'worker_num' => 2,
    'max_request' => 10000,
    #'log_file' => dirname(__DIR__) . '/runtime/swoole.log',
    'entrance' => '\App\App::process',
];

//start stop reload restart
$operate = isset($argv[1]) ? $argv[1] : 'start';

$class = new \Swover\Server($config);
$class->$operate();
