<?php

namespace App\Queue;

use App\Utils\Queue;
use Ruesin\Utils\Redis;

class Select extends Base
{
    public function handle()
    {
        $info = $this->getQueueInfo();

        if (isset($info['config']['host'])) {
            $message = Redis::createInstance($info['list_name'], $info['config'])
                ->lpop($info['list_name']);
            return self::response(200, ['messageId' => 'custom-active-queue', 'content' => $message]);
        }

        $messageId = Queue::getActiveInstance()->lpop(Queue::activeName($this->queue_name));
        if (!$messageId) {
            return self::response(200, ['messageId' => '', 'content' => ''], 'Message is empty!');
        }

        $message = $this->defaultInstance->hget(Queue::messageName($this->queue_name), $messageId);
        if (!$message) {
            return self::response(400, ['messageId' => $messageId], 'Message body does not exist!');
        }

        Queue::getReadInstance()->zadd(Queue::readName($this->queue_name), date('YmdHis', time() + $info['hide_time']), $messageId);

        return self::response(200, ['messageId' => $messageId, 'content' => $message]);
    }

}