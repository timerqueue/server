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
        $this->defaultInstance->hdel($this->messageName, $this->data['messageId']);
        //延迟
        $this->defaultInstance->zrem($this->delayName, $this->data['messageId']);
        //已读
        $this->defaultInstance->zrem($this->readName, $this->data['messageId']);
        //活跃
        $this->defaultInstance->lrem($this->activeName, 0, $this->data['messageId']);

        return self::response(200, [], 'Message deleted successfully!');
    }
}