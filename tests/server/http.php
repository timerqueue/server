<?php
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

$config = [
    'server_type' => 'http',
    'daemonize' => true,
    'process_name' => 'dq_http',
    'host' => '0.0.0.0',
    'port' => '9501',
    'worker_num' => 2,
    'max_request' => 10000,
    #'log_file' => dirname(__DIR__) . '/runtime/swoole_http.log',
    'entrance' => '\App\App::http',
];

//start stop reload restart
$operate = isset($argv[1]) ? $argv[1] : 'start';

$class = new \Swover\Server($config);
$class->$operate();
