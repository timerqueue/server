<?php
namespace App\Queue;

use App\Utils\Queue;
use Ruesin\Utils\Redis;

class Wakeup extends Base
{
    public function handle()
    {
        $delays = Queue::getDelayInstance()->zrangebyscore(Queue::delayName($this->queue_name), 0, $this->data['score']);

        if (empty($delays)) {
            return true;
        }

        $info = $this->data['info'];

        if (isset($info['config']['host'])) {
            $activeInstance = Redis::createInstance($this->queue_name, $info['config']);
            foreach ($delays as $messageId) {
                if ($message = Queue::getDefaultInstance()->hget(Queue::messageName($this->queue_name), $messageId)) {
                    $activeInstance->rpush($info['list_name'], $message);
                    Queue::getDefaultInstance()->hdel(Queue::messageName($this->queue_name), $messageId);
                }
            }
        } else {
            foreach ($delays as $messageId) {
                if (Queue::getDefaultInstance()->hexists(Queue::messageName($this->queue_name), $messageId)) {
                    Queue::getActiveInstance()->rpush(Queue::activeName($this->queue_name), $messageId);
                }
            }
        }

        Queue::getDelayInstance()->zremrangebyscore(Queue::delayName($this->queue_name), 0, $this->data['score']);
        return true;
    }
}