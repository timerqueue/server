<?php

namespace App\Queue;

use Ruesin\Utils\Redis;

class Wakeup extends Base
{
    public function handle()
    {
        $delays = $this->defaultInstance->zrangebyscore($this->delayName, 0, $this->data['score']);

        if (empty($delays)) return true;

        $info = $this->data['info'];

        if (isset($info['config']['host'])) {
            $activeInstance = Redis::createInstance($this->queue_name, $info['config']);
            foreach ($delays as $messageId) {
                if ($message = $this->defaultInstance->hget($this->messageName, $messageId)) {
                    $activeInstance->rpush($info['list_name'], $message);
                    $this->defaultInstance->hdel($this->messageName, $messageId);
                }
            }
        } else {
            foreach ($delays as $messageId) {
                if ($this->defaultInstance->hexists($this->messageName, $messageId)) {
                    $this->defaultInstance->rpush($this->activeName, $messageId);
                }
            }
        }

        $this->defaultInstance->zremrangebyscore($this->delayName, 0, $this->data['score']);
        return true;
    }
}