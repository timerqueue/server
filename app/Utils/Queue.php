<?php

namespace App\Utils;

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
}