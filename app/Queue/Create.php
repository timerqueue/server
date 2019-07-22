<?php
namespace App\Queue;

use App\Utils\Queue;

class Create extends Base
{
    public function handle()
    {
        if (!isset($this->data['delay_time']) || intval($this->data['delay_time']) <= 0) {
            return self::response(400, [], 'Param delay_time error!');
        }

        $information = [
            'delay_time' => intval($this->data['delay_time']),
            'hide_time'  => isset($this->data['hide_time']) ? max(intval($this->data['hide_time']), 30) : 30
        ];

        if (($this->data['config'] ?? false) && ($this->data['list_name'] ?? false)) {
            $information['list_name'] = $this->data['list_name'];
            $information['config'] = $this->data['config'];
        }

        if ($this->defaultInstance->hsetnx(Queue::queueInfoName(), $this->queue_name, json_encode($information, JSON_UNESCAPED_UNICODE))) {
            $this->defaultInstance->rpush(Queue::queueListName(), $this->queue_name);
        } else {
            return self::response(400, [], 'Queue already exists!');
        }
        return self::response(200, [], 'Queue created successfully!');
    }
}