<?php
return [
    'process' => [
        'server_type' => 'process',
        'daemonize' => false,
        'process_name' => 'dq_process',
        'worker_num' => 2,
        'max_request' => 10000,
        'log_file' => dirname(__DIR__) . '/runtime/swoole.log',
        'entrance' => '\App\App::process',
    ],

    'http' => [
        'server_type' => 'http',
        'daemonize' => false,
        'process_name' => 'dq_http',
        'host' => '127.0.0.1', //'0.0.0.0',
        'port' => '9501',
        'worker_num' => 2,
        'max_request' => 10000,
        'log_file' => dirname(__DIR__) . '/runtime/swoole_http.log',
        'entrance' => '\App\App::http',
    ]
];
