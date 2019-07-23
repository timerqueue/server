<?php

namespace App\Queue;

use App\Utils\Queue;

class Insert extends Base
{
    public function handle()
    {
        if (!isset($this->data['message']) || !is_string($this->data['message'])) {
            return self::response(400, [], 'Message body error!');
        }

        $delay_time = intval($this->data['delay_time'] ?? 0);

        $info = Queue::getInfo($this->queue_name);

        //创建队列
        if (empty($info)) {
            $queue_time = $delay_time > 0 ? $delay_time : 30;
            (new Create(['queue_name' => $this->queue_name, 'data' => ['delay_time' => $queue_time]]))->handle();
            $info['delay_time'] = $queue_time;
        }

        $deliverTime = time() + ($delay_time > 0 ? $delay_time : $info['delay_time']);

        //TODO 有效性校验
        if (isset($this->data['deliver_time'])) {
            $deliverTime = intval(substr($this->data['deliver_time'], 0, 10));
        }

        if ($deliverTime < time()) {
            return self::response(400, [], 'Delay time less than 0!');
        }

        do {
            $messageId = md5(uniqid(microtime(true) . $this->queue_name . mt_rand(), true));
        } while (!$this->defaultInstance->hsetnx($this->messageName, $messageId, $this->data['message']));

        Queue::getDelayInstance()->zadd($this->delayName, date('YmdHis', $deliverTime), $messageId);
        return self::response(200, ['messageId' => $messageId], 'Message sent successfully!');
    }
}