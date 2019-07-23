<?php
namespace App\Queue;

use App\Utils\Queue;

class Drop extends Base
{
    public function handle()
    {
        //删除消息体
        $this->defaultInstance->del([Queue::messageName($this->queue_name)]);
        //删除延时消息
        Queue::getDelayInstance()->del([Queue::delayName($this->queue_name)]);
        //删除已读消息
        Queue::getReadInstance()->del([Queue::readName($this->queue_name)]);
        //删除活跃消息
        Queue::getActiveInstance()->del([Queue::activeName($this->queue_name)]);

        //删除队列定义
        $this->defaultInstance->hdel(Queue::queueInfoName(), $this->queue_name);
        $this->defaultInstance->lrem(Queue::queueListName(), 0, $this->queue_name);

        //删除消息体
        $this->defaultInstance->del([Queue::messageName($this->queue_name)]);
        //删除延时消息
        Queue::getDelayInstance()->del([Queue::delayName($this->queue_name)]);
        //删除已读消息
        Queue::getReadInstance()->del([Queue::readName($this->queue_name)]);
        //删除活跃消息
        Queue::getActiveInstance()->del([Queue::activeName($this->queue_name)]);

        return self::response(200, [], 'Queue deleted successfully!');
    }
}