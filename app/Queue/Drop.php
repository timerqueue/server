<?php
namespace App\Queue;

use App\Utils\Queue;

class Drop extends Base
{
    public function handle()
    {
        //删除消息体
        $this->defaultInstance->del([$this->messageName]);
        //删除延时消息
        Queue::getDelayInstance()->del([$this->delayName]);
        //删除已读消息
        Queue::getReadInstance()->del([$this->readName]);
        //删除活跃消息
        Queue::getActiveInstance()->del([$this->activeName]);

        //删除队列定义
        $this->defaultInstance->hdel(Queue::queueInfoName(), $this->queue_name);
        $this->defaultInstance->lrem(Queue::queueListName(), 0, $this->queue_name);

        //删除消息体
        $this->defaultInstance->del([$this->messageName]);
        //删除延时消息
        Queue::getDelayInstance()->del([$this->delayName]);
        //删除已读消息
        Queue::getReadInstance()->del([$this->readName]);
        //删除活跃消息
        Queue::getActiveInstance()->del([$this->activeName]);

        return self::response(200, [], 'Queue deleted successfully!');
    }
}