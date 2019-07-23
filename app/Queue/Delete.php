<?php

namespace App\Queue;

use App\Utils\Connection;

class Delete extends Base
{
    public function handle()
    {
        if (!isset($this->data['messageId'])) {
            return self::response(400, [], 'messageId does not exist!');
        }

        //消息体
        $this->defaultInstance->hdel($this->messageName, $this->data['messageId']);
        //延迟
        Connection::delay()->zrem($this->delayName, $this->data['messageId']);
        //已读
        Connection::read()->zrem($this->readName, $this->data['messageId']);
        //活跃
        Connection::active()->lrem($this->activeName, 0, $this->data['messageId']);

        return self::response(200, [], 'Message deleted successfully!');
    }
}