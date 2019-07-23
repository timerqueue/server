<?php

namespace App\Queue;

class Timeout extends Base
{
    public function handle()
    {
        $read = $this->connection->zrangebyscore($this->readName, 0, $this->data['score']);

        if (empty($read)) {
            return true;
        }

        foreach ($read as $value) {
            if ($this->connection->hexists($this->messageName, $value)) {
                $this->connection->rpush($this->activeName, $value);
            }
        }

        $this->connection->zremrangebyscore($this->readName, 0, $this->data['score']);
        return true;
    }
}