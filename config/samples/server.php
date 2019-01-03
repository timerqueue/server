<?php
return [
    'process' => [
        'server_type' => 'process',
        'daemonize' => true,
        'process_name' => 'dq_process',
        'worker_num' => 2,
        'max_request' => 0,
        'log_file' => dirname(__DIR__).'/runtime/swoole.log',
        'entrance' => '\App\Process::run',
    ],

    'http' => [
        'server_type' => 'http',
        'daemonize' => false,
        'process_name' => 'dq_http',
        'host' => '127.0.0.1',
        'port' => '9501',
        'worker_num' => 2,
        'task_worker_num' => 1,
        'max_request' => 10000,
        'log_file' => dirname(__DIR__).'/runtime/swoole_http.log',
        'entrance'    => '\App\Sockets::run',
        'async'    => false,
        'signature'   => '\App\Utils\Sign::verify',
        'trace_log'   => true
    ],

    'tcp' => [
        'server_type' => 'tcp',
        'daemonize' => false,
        'process_name' => 'dq_tcp',
        'host' => '127.0.0.1',
        'port' => '9502',
        'worker_num' => 2,
        'task_worker_num' => 1,
        'max_request' => 10000,
        'log_file' => dirname(__DIR__).'/runtime/swoole_tcp.log',
        'entrance'    => '\App\Sockets::run',
        'async'    => false,
        'signature'   => '\App\Utils\Sign::verify',
        'trace_log'   => true
    ],
];
