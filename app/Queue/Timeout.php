<?php

namespace App\Queue;

class Timeout extends Base
{
    public function handle()
    {
        $this->connection->transaction(function ($tx) {
            $read = $tx->zrangebyscore($this->readName, 0, $this->data['score']);
            if (empty($read)) {
                return;
            }
            foreach ($read as $value) {
                if ($tx->hexists($this->messageName, $value)) {
                    $tx->rpush($this->activeName, $value);
                }
            }
            $tx->zremrangebyscore($this->readName, 0, $this->data['score']);
            return;
        });

        return true;
    }
}