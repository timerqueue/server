<?php

namespace App\Utils;

use App\Queue\Timeout;
use App\Queue\Wakeup;
use Ruesin\Utils\Config;

class Queue
{
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
        $info = Connection::default()->hget(self::queueInfoName(), $queueName);
        return $info ? json_decode($info, true) : [];
    }

    /**
     * 激活消息
     * @param $queueName
     * @param $queueInfo
     * @return bool
     */
    public static function active($queueName, $queueInfo)
    {
        if (!Queue::lock($queueName)) false;
        $request = [
            'queue_name' => $queueName,
            'data' => [
                'info' => json_decode($queueInfo, true),
                'score' => date('YmdHis')
            ]
        ];

        (new Wakeup($request))->handle();
        (new Timeout($request))->handle();
        Queue::unlock($queueName);
        return true;
    }

    /**
     * 抢占对队列操作的锁
     * @param $queueName
     * @return int
     */
    public static function lock($queueName)
    {
        return Connection::default()->setnx($queueName, 1);
    }

    /**
     * 释放锁
     * @param $queueName
     * @return int
     */
    public static function unlock($queueName)
    {
        return Connection::default()->del($queueName);
    }
}