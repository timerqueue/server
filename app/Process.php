<?php

namespace App;

use App\Utils\Queue;
use Ruesin\Utils\Redis;

class Process
{
    public static function run()
    {
        sleep(1);
        $result = true;

        $name = Queue::getDefaultInstance()->lpop(Queue::queueListName());
        if (!$name) {
            return $result;
        }

        try {
            $info = Queue::getDefaultInstance()->hget(Queue::queueInfoName(), $name);
            if (!$info) {
                return $result;
            }

            $info = json_decode($info, true);
            $score = date('YmdHis');

            self::delayToActive($name, $score, $info);
            self::readToActive($name, $score);

        } catch (\Exception $e) {
            $result = false;

        }

        if (Queue::getDefaultInstance()->hexists(Queue::queueInfoName(), $name)) {
            Queue::getDefaultInstance()->rpush(Queue::queueListName(), $name);
        }

        return $result;
    }

    /**
     * 延时消息到期后转入活跃消息队列
     */
    private static function delayToActive($name, $score, $info)
    {
        $delays = Queue::getDelayInstance()->zrangebyscore(Queue::delayName($name), 0, $score);

        if (empty($delays)) {
            return true;
        }

        if (isset($info['config']['host'])) {
            Redis::setConfig($name, $info['config']); //TODO
            $activeInstance = Redis::getInstance($name);
            foreach ($delays as $messageId) {
                if ($message = Queue::getDefaultInstance()->hget(Queue::messageName($name), $messageId)) {
                    $activeInstance->rpush($info['list_name'], $message);
                    Queue::getDefaultInstance()->hdel(Queue::messageName($name), $messageId);
                }
            }
            Redis::close($name);
        } else {
            foreach ($delays as $messageId) {
                if (Queue::getDefaultInstance()->hexists(Queue::messageName($name), $messageId)) {
                    Queue::getActiveInstance()->rpush(Queue::activeName($name), $messageId);
                }
            }
        }

        //和 ZREM 复杂度对比
        Queue::getDelayInstance()->zremrangebyscore(Queue::delayName($name), 0, $score);
        return true;
    }

    /**
     * 已读消息超时后放回活跃消息队列
     */
    private static function readToActive($name, $score)
    {
        $read = Queue::getReadInstance()->zrangebyscore(Queue::readName($name), 0, $score);

        if (empty($read)) {
            return true;
        }

        foreach ($read as $value) {
            if (Queue::getDefaultInstance()->hexists(Queue::messageName($name), $value)) {
                Queue::getActiveInstance()->rpush(Queue::activeName($name), $value);
            }
        }

        Queue::getReadInstance()->zremrangebyscore(Queue::readName($name), 0, $score);
        return true;
    }
}