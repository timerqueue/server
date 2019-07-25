<?php

namespace App\Queue;

use App\Utils\Queue;

/**
 * 创建延时队列
 *
 * @package App\Queue
 */
class Create extends Base
{
    public function handle()
    {
        $delay_time = intval($this->data['delay_time'] ?? 0);
        if ($delay_time <= 0) {
            return self::response(400, [], 'Param delay_time error!');
        }

        $information = [
            'delay_time' => $delay_time > 0 ? $delay_time : 30,
            'hide_time' => max(intval($this->data['hide_time'] ?? 0), 30), //隐藏时间最小30秒
        ];

        if (($this->data['config'] ?? false) && ($this->data['list_name'] ?? false)) {
            $information['list_name'] = $this->data['list_name'];
            $information['config'] = $this->data['config'];
        }

        if ($this->connection->hsetnx(Queue::queueInfoName(), $this->queue_name, json_encode($information, JSON_UNESCAPED_UNICODE))) {
            $this->connection->rpush(Queue::queueListName(), $this->queue_name);
        } else {
            return self::response(400, [], 'Queue already exists!');
        }
        return self::response(200, [], 'Queue created successfully!');
    }
}