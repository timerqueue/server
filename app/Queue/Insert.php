<?php

namespace App\Queue;

use App\Utils\Queue;
use Ruesin\Utils\Redis;

class Insert extends Base
{
    public function handle()
    {
        if (!isset($this->data['message']) || !is_string($this->data['message'])) {
            return self::response(400, [], 'Message body error!');
        }

        //TODO 队列不存在时创建队列
        $info = $this->getQueueInfo();

        if (isset($this->data['delay_time']) && $this->data['delay_time'] > 0) {
            $delay = $this->data['delay_time'];
        } else {
            $delay = $info['delay_time'];
        }
        $deliverTime = time() + $delay;

        if (isset($this->data['deliver_time'])) {
            $deliverTime = intval(substr($this->data['deliver_time'], 0, 10)); //todo 时间戳  长度判断
        }

        if ($deliverTime < time()) {
            return self::response(400, [], 'Delay time less than 0!');
        }

        do {
            $messageId = md5(uniqid(microtime(true) . $this->queue_name . mt_rand(), true));
        } while (!$this->defaultInstance->hsetnx(Queue::messageName($this->queue_name), $messageId, $this->data['message']));

        Queue::getDelayInstance()->zadd(Queue::delayName($this->queue_name), date('YmdHis', $deliverTime), $messageId);
        return self::response(200, ['messageId' => $messageId], 'Message sent successfully!');
    }
}