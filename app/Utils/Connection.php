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
        return self::getConnection('default');
    }

    /**
     * 获取延时 Redis 实例
     * @return \Predis\Client
     */
    public static function delay()
    {
        return self::getConnection('delay');
    }

    /**
     * 获取活跃 Redis 实例
     * @return \Predis\Client
     */
    public static function active()
    {
        return self::getConnection('active');
    }

    /**
     * 获取已读 Redis 实例
     * @return \Predis\Client
     */
    public static function read()
    {
        return self::getConnection('read');
    }

    /**
     * 获取指定key的Redis实例
     * @param $key
     * @return \Predis\Client|Redis
     */
    private static function getConnection($key)
    {
        return Redis::getInstance($key);
    }
}