<?php

namespace App\Queue;

use App\Utils\Queue;

/**
 * 删除延时队列
 *
 * @package App\Queue
 */
class Drop extends Base
{
    public function handle()
    {
        for ($i = 0; $i < 5; $i++) {
            if (!Queue::lock($this->queue_name))
                usleep(50000);
        }

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