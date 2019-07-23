<?php

namespace App\Queue;

class Timeout extends Base
{
    public function handle()
    {
        $read = $this->defaultInstance->zrangebyscore($this->readName, 0, $this->data['score']);

        if (empty($read)) {
            return true;
        }

        foreach ($read as $value) {
            if ($this->defaultInstance->hexists($this->messageName, $value)) {
                $this->defaultInstance->rpush($this->activeName, $value);
            }
        }

        $this->defaultInstance->zremrangebyscore($this->readName, 0, $this->data['score']);
        return true;
    }
}