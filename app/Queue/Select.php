<?php

namespace App\Queue;

use App\Utils\Queue;
use Ruesin\Utils\Redis;

/**
 * 获取延时消息
 *
 * @package App\Queue
 */
class Select extends Base
{
    private $response = '';

    public function handle()
    {
        $info = Queue::getInfo($this->queue_name);

        if (empty($info)) {
            return self::response(400, ['messageId' => '', 'content' => ''], 'Queue does not exist!');
        }

        for ($i = 1; $i <= 2; $i++) {
            if (isset($info['config']['host'])) {
                if ($this->thirdMessage($info)) break;
            } else {
                if ($this->readMessage($info)) break;
            }
            if ($i == 1)
                Queue::active($this->queue_name, $info);
        }

        return $this->response;
    }

    private function readMessage($info)
    {
        $this->connection->multi();
        $messageId = $this->connection->lpop($this->activeName);
        if (!$messageId) {
            $this->connection->discard();
            $this->response = self::response(200, ['messageId' => '', 'content' => ''], 'Message is empty!');
            return false;
        }

        $message = $this->connection->hget($this->messageName, $messageId);
        if (!$message) {
            $this->connection->discard();
            $this->response = self::response(400, ['messageId' => $messageId], 'Message body does not exist!');
            return false;
        }

        $this->connection->zadd($this->readName, date('YmdHis', time() + $info['hide_time']), $messageId);
        $this->connection->exec();
        $this->response = self::response(200, ['messageId' => $messageId, 'content' => $message]);
        return true;
    }

    private function thirdMessage($info)
    {
        $message = Redis::createInstance($info['list_name'], $info['config'])
            ->lpop($info['list_name']);
        if ($message) {
            $this->response = self::response(200, ['messageId' => 'custom-active-queue', 'content' => $message]);
            return false;
        }
        $this->response = self::response(200, ['messageId' => '', 'content' => ''], 'Message is empty!');
        return true;
    }
}