<?php
namespace App\Queue;

use App\Utils\Queue;

class Timeout extends Base
{
    public function handle()
    {
        $read = Queue::getReadInstance()->zrangebyscore($this->readName, 0, $this->data['score']);

        if (empty($read)) {
            return true;
        }

        foreach ($read as $value) {
            if (Queue::getDefaultInstance()->hexists($this->messageName, $value)) {
                Queue::getActiveInstance()->rpush($this->activeName, $value);
            }
        }

        Queue::getReadInstance()->zremrangebyscore($this->readName, 0, $this->data['score']);
        return true;
    }
}