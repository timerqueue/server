<?php
namespace App\Queue;

use App\Utils\Queue;

class Timeout extends Base
{
    public function handle()
    {
        $read = Queue::getReadInstance()->zrangebyscore(Queue::readName($this->queue_name), 0, $this->data['score']);

        if (empty($read)) {
            return true;
        }

        foreach ($read as $value) {
            if (Queue::getDefaultInstance()->hexists(Queue::messageName($this->queue_name), $value)) {
                Queue::getActiveInstance()->rpush(Queue::activeName($this->queue_name), $value);
            }
        }

        Queue::getReadInstance()->zremrangebyscore(Queue::readName($this->queue_name), 0, $this->data['score']);
        return true;
    }
}