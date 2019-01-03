<?php
namespace App\Utils;

use Swover\Utils\Config;

class Queue
{
    /**
     * @var \Predis\ClientInterface
     */
    private static $defaultInstance = null;
    /**
     * @var \Predis\ClientInterface
     */
    private static $activeInstance = null;
    /**
     * @var \Predis\ClientInterface
     */
    private static $delayInstance = null;
    /**
     * @var \Predis\ClientInterface
     */
    private static $readInstance = null;

    public static function queueListName()
    {
        return Config::get('queue.list_name');
    }

    public static function queueInfoName()
    {
        return Config::get('queue.info_name');
    }

    public static function delayName($queue_name)
    {
        return $queue_name.Config::get('queue.delay_suffix');
    }

    public static function messageName($queue_name)
    {
        return $queue_name.Config::get('queue.message_suffix');
    }

    public static function activeName($queue_name)
    {
        return $queue_name.Config::get('quque.active_suffix');
    }

    public static function readName($queue_name)
    {
        return $queue_name.Config::get('queue.read_suffix');
    }

    /**
     * 获取默认 Redis 实例
     * @return \Predis\ClientInterface
     */
    public static function getDefaultInstance()
    {
        if (self::$defaultInstance == null) {
            self::$defaultInstance = Redis::instance(self::getRedisKey('default'));
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
            self::$delayInstance = Redis::instance(self::getRedisKey('delay'));
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
            self::$activeInstance = Redis::instance(self::getRedisKey('active'));
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
            self::$readInstance = Redis::instance(self::getRedisKey('active'));
        }
        return self::$readInstance;
    }

    /**
     * 获取指定Redis配置文件的key
     */
    private static function getRedisKey($key)
    {
        return Config::get('queue.redis_key.'.$key, $key);
    }
}