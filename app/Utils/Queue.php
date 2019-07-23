<?php

namespace App\Utils;

use Ruesin\Utils\Config;
use Ruesin\Utils\Redis;

class Queue
{
    private static $defaultInstance = null;
    private static $activeInstance = null;
    private static $delayInstance = null;
    private static $readInstance = null;

    public static function queueListName()
    {
        return Config::get('queue.list_name');
    }

    public static function queueInfoName()
    {
        return Config::get('queue.info_name');
    }

    /**
     * 获取队列信息
     * @param string $queueName 队列名
     * @return array
     */
    public static function getInfo(string $queueName)
    {
        $info = Queue::getDefaultInstance()->hget(Queue::queueInfoName(), $queueName);
        return $info ? json_decode($info, true) : [];
    }

    /**
     * 获取默认 Redis 实例
     * @return \Predis\ClientInterface
     */
    public static function getDefaultInstance()
    {
        if (self::$defaultInstance == null) {
            self::$defaultInstance = self::getRedis('default');
        }
        return self::$defaultInstance;
    }

    /**
     * 获取延时 Redis 实例
     * @return \Predis\ClientInterface
     */
    public static function getDelayInstance()
    {
        if (self::$delayInstance == null) {
            self::$delayInstance = self::getRedis('delay');
        }
        return self::$delayInstance;
    }

    /**
     * 获取活跃 Redis 实例
     * @return \Predis\ClientInterface
     */
    public static function getActiveInstance()
    {
        if (self::$activeInstance == null) {
            self::$activeInstance = self::getRedis('active');
        }
        return self::$activeInstance;
    }

    /**
     * 获取已读 Redis 实例
     * @return \Predis\ClientInterface
     */
    public static function getReadInstance()
    {
        if (self::$readInstance == null) {
            self::$readInstance = self::getRedis('read');
        }
        return self::$readInstance;
    }

    /**
     * 获取指定key的Redis实例
     */
    private static function getRedis($key)
    {
        return Redis::getInstance($key);
    }
}