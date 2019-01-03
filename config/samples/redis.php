<?php
return [
    //队列列表，队列定义，消息体
    'default' => [
        'host'      => '127.0.0.1',
        'port'      => '6379',
        'database'  => '0',
        'username'  => '',
        'password'  => '',
        'prefix'    => 'online:'
    ],
    //延迟消息
    'delay' => [
        'host'      => '127.0.0.1',
        'port'      => '6379',
        'database'  => '0',
        'username'  => '',
        'password'  => '',
        'prefix'    => 'delay:'
    ],
    //活跃消息
    'active' => [
        'host'      => '127.0.0.1',
        'port'      => '6379',
        'database'  => '0',
        'username'  => '',
        'password'  => '',
        'prefix'    => 'active:'
    ],
    //已读消息
    'read' => [
        'host'      => '127.0.0.1',
        'port'      => '6379',
        'database'  => '0',
        'username'  => '',
        'password'  => '',
        'prefix'    => 'read:'
    ],
];
