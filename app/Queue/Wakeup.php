<?php
namespace App\Queue;

use App\Utils\Queue;
use Ruesin\Utils\Redis;

class Wakeup extends Base
{
    public function handle()
    {
        $delays = Queue::getDelayInstance()->zrangebyscore($this->delayName, 0, $this->data['score']);

        if (empty($delays)) {
            return true;
        }

        $info = $this->data['info'];

        if (isset($info['config']['host'])) {
            $activeInstance = Redis::createInstance($this->queue_name, $info['config']);
            foreach ($delays as $messageId) {
                if ($message = Queue::getDefaultInstance()->hget($this->messageName, $messageId)) {
                    $activeInstance->rpush($info['list_name'], $message);
                    Queue::getDefaultInstance()->hdel($this->messageName, $messageId);
                }
            }
        } else {
            foreach ($delays as $messageId) {
                if (Queue::getDefaultInstance()->hexists($this->messageName, $messageId)) {
                    Queue::getActiveInstance()->rpush($this->activeName, $messageId);
                }
            }
        }

        Queue::getDelayInstance()->zremrangebyscore($this->delayName, 0, $this->data['score']);
        return true;
    }
}