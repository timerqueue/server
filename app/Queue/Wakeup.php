<?php

namespace App\Queue;

use Ruesin\Utils\Redis;

class Wakeup extends Base
{
    public function handle()
    {
        $delays = $this->connection->zrangebyscore($this->delayName, 0, $this->data['score']);

        if (empty($delays)) return true;

        $info = $this->data['info'];

        if (isset($info['config']['host'])) {
            $activeInstance = Redis::createInstance($this->queue_name, $info['config']);
            foreach ($delays as $messageId) {
                if ($message = $this->connection->hget($this->messageName, $messageId)) {
                    $activeInstance->rpush($info['list_name'], $message);
                    $this->connection->hdel($this->messageName, $messageId);
                }
            }
        } else {
            foreach ($delays as $messageId) {
                if ($this->connection->hexists($this->messageName, $messageId)) {
                    $this->connection->rpush($this->activeName, $messageId);
                }
            }
        }

        $this->connection->zremrangebyscore($this->delayName, 0, $this->data['score']);
        return true;
    }
}