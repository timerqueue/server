<?php
return [
    //所有队列的列表
    'list_name' => 'delay:queue_list',
    //队列定义哈希
    'info_name' => 'delay:queue_info',
    //消息体哈希后缀
    'message_suffix' => '_message_hash',
    //延迟集合后缀
    'delay_suffix' => '_delay_zeset',
    //活跃队列后缀
    'active_suffix' => '_active_list',
    //已读集合后缀
    'read_suffix' => '_read_zset',
    //各消息使用的redis配置
    'redis_key' => [
        'default' => 'default',
        'delay'   => 'delay',
        'active'  => 'active',
        'read'    => 'read',
    ]
];