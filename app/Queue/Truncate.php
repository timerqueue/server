<?php
namespace App\Queue;

use App\Utils\Queue;

/**
 * 清空队列
 */
class Truncate extends Base
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
            ]);
        return self::response(200, [], 'Queue truncate successfully!');
    }
}