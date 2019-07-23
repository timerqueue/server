<?php

namespace App\Queue;

use App\Utils\Connection;

class Timeout extends Base
{
    public function handle()
    {
        $read = Connection::read()->zrangebyscore($this->readName, 0, $this->data['score']);

        if (empty($read)) {
            return true;
        }

        foreach ($read as $value) {
            if ($this->defaultInstance->hexists($this->messageName, $value)) {
                Connection::active()->rpush($this->activeName, $value);
            }
        }

        Connection::read()->zremrangebyscore($this->readName, 0, $this->data['score']);
        return true;
    }
}