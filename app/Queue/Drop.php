<?php

namespace App\Queue;

use App\Utils\Queue;

class Drop extends Base
{
    public function handle()
    {
        $this->connection->transaction()
            ->del([
                $this->messageName,
                $this->delayName,
                $this->readName,
                $this->activeName
            ])->hdel(Queue::queueInfoName(), $this->queue_name)
            ->lrem(Queue::queueListName(), 0, $this->queue_name)
            ->del([
                $this->messageName,
                $this->delayName,
                $this->readName,
                $this->activeName
            ]);
        return self::response(200, [], 'Queue deleted successfully!');
    }
}