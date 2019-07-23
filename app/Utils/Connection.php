<?php

namespace App\Utils;

use Ruesin\Utils\Redis;

class Connection
{
    /**
     * 获取默认 Redis 实例
     * @return \Predis\Client
     */
    public static function default()
    {
        return Redis::getInstance('default');
    }
}