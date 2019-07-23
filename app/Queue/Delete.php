<?php

namespace App\Queue;

class Delete extends Base
{
    public function handle()
    {
        if (!isset($this->data['messageId'])) {
            return self::response(400, [], 'messageId does not exist!');
        }

        //消息体
        $this->connection->hdel($this->messageName, $this->data['messageId']);
        //延迟
        $this->connection->zrem($this->delayName, $this->data['messageId']);
        //已读
        $this->connection->zrem($this->readName, $this->data['messageId']);
        //活跃
        $this->connection->lrem($this->activeName, 0, $this->data['messageId']);

        return self::response(200, [], 'Message deleted successfully!');
    }
}