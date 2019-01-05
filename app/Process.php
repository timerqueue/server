<?php
namespace App;

use App\Utils\Queue;
use App\Utils\Redis;

class Process
{
    public static function run()
    {
        $name = Queue::getDefaultInstance()->lpop(Queue::queueListName());
        if (!$name) {
            sleep(1);
            return true;
        }

        try {
            $info = Queue::getDefaultInstance()->hget(Queue::queueInfoName(), $name);
            if (! $info ) {
                sleep(1);
                return true;
            }

            $info = json_decode($info, true);

            $score = time();

            self::delayToActive($name, $score, $info);
            self::readToActive($name, $score);

        } catch (\Exception $e) {
            if ( Queue::getDefaultInstance()->hexists(Queue::queueInfoName(), $name) ) {
                Queue::getDefaultInstance()->rpush(Queue::queueListName(), $name);
            }
            sleep(1);
            throw $e;
        }

        if ( Queue::getDefaultInstance()->hexists(Queue::queueInfoName(), $name) ) {
            Queue::getDefaultInstance()->rpush(Queue::queueListName(), $name);
        }
        sleep(1);

        return true;
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
            $activeInstance  = Redis::instance('', $info['config']);
            foreach ($delays as $messageId) {
                if ($message = Queue::getDefaultInstance()->hget(Queue::messageName($name), $messageId)) {
                    $activeInstance->rpush($info['list_name'], $message);
                }
            }
            Redis::close('', $info['config']);
        } else {
            foreach ($delays as $messageId) {
                if (Queue::getDefaultInstance()->hexists(Queue::messageName($name), $messageId)) {
                    Queue::getActiveInstance()->rpush(Queue::activeName($name), $messageId);
                }
            }
        }

        Queue::getDelayInstance()->zremrangebyscore(Queue::delayName($name), 0, $score);
        return true;
    }

    /**
     * 已读消息超时后转入活跃消息队列
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