<?php

namespace App\Queue;

/**
 * 删除指定消息
 *
 * @package App\Queue
 */
class Delete extends Base
{
    public function handle()
    {
        if (!isset($this->data['messageId'])) {
            return self::response(400, [], 'messageId does not exist!');
        }

        $this->connection->transaction()
            ->hdel($this->messageName, $this->data['messageId'])
            ->zrem($this->delayName, $this->data['messageId'])
            ->zrem($this->readName, $this->data['messageId'])
            ->lrem($this->activeName, 0, $this->data['messageId']);

        return self::response(200, [], 'Message deleted successfully!');
    }
}