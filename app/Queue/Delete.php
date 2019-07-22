<?php
namespace App\Queue;

use App\Utils\Queue;
use Ruesin\Utils\Redis;

class Delete extends Base
{
    /**
     * 删除消息
     * @return bool
     * @throws \Exception
     */
    public function handle()
    {
        if (!isset($this->data['messageId'])) {
            return self::response(400, [], 'messageId does not exist!');
        }

        //消息体
        $this->defaultInstance->hdel(Queue::messageName($this->queue_name), $this->data['messageId']);
        //延迟
        Queue::getDelayInstance()->zrem(Queue::delayName($this->queue_name), $this->data['messageId']);
        //已读
        Queue::getReadInstance()->zrem(Queue::readName($this->queue_name), $this->data['messageId']);
        //活跃
        Queue::getActiveInstance()->lrem(Queue::activeName($this->queue_name), 0, $this->data['messageId']);

        return self::response(200, [], 'Message deleted successfully!');
    }
}