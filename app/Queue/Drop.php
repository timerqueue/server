<?php

namespace App\Queue;

use App\Utils\Connection;
use App\Utils\Queue;

class Drop extends Base
{
    public function handle()
    {
        //删除消息体
        $this->connection->del([$this->messageName]);
        //删除延时消息
        $this->connection->del([$this->delayName]);
        //删除已读消息
        $this->connection->del([$this->readName]);
        //删除活跃消息
        $this->connection->del([$this->activeName]);

        //删除队列定义
        $this->connection->hdel(Queue::queueInfoName(), $this->queue_name);
        $this->connection->lrem(Queue::queueListName(), 0, $this->queue_name);

        //删除消息体
        $this->connection->del([$this->messageName]);
        //删除延时消息
        $this->connection->del([$this->delayName]);
        //删除已读消息
        $this->connection->del([$this->readName]);
        //删除活跃消息
        $this->connection->del([$this->activeName]);

        return self::response(200, [], 'Queue deleted successfully!');
    }
}