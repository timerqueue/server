<?php
namespace App;

use App\Utils\Queue;

class Process
{
    public static function run()
    {
        $name = Queue::getDefaultInstance()->lpop(Queue::queueListName());
        if (!$name) {
            sleep(1);
            return true;
        }

        $info = Queue::getDefaultInstance()->hget(Queue::queueInfoName(), $name);
        if (! $info ) {
            sleep(1);
            return true;
        }

        $score = time();

        self::delayToActive($name, $score);
        self::readToActive($name, $score);

        if ( Queue::getDefaultInstance()->hexists(Queue::queueInfoName(), $name) ) {
            Queue::getDefaultInstance()->rpush(Queue::queueListName(), $name);
        }

        sleep(1);
        return true;
    }

    /**
     * 延时消息到期后转入活跃消息队列
     */
    private static function delayToActive($name, $score)
    {
        $delays = Queue::getDelayInstance()->zrangebyscore(Queue::delayName($name), 0, $score);

        if (empty($delays)) {
            return true;
        }

        foreach ($delays as $value) {
            if (Queue::getDefaultInstance()->hexists(Queue::messageName($name), $value)) {
                Queue::getActiveInstance()->rpush(Queue::activeName($name), $value);
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