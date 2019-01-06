<?php
namespace App;

use App\Utils\Queue;
use App\Utils\Redis;

class Handle
{
    private $queue_name = '';

    private $data = [];

    public function __construct($request)
    {
        $this->queue_name = $request['queue_name'];
        if (isset($request['data'])) {
            $this->data = $request['data'];
        }
    }

    /**
     * 创建队列
     */
    public function createQueue()
    {
        if (!isset($this->data['delay_time']) || intval($this->data['delay_time']) <= 0) {
            return self::response(400, [], 'Param delay_time error!');
        }

        $information = [
            'delay_time' => intval($this->data['delay_time']),
            'hide_time'  => isset($this->data['hide_time']) ? max(intval($this->data['hide_time']), 30) : 30
        ];

        if (isset($this->data['config']) && !empty($this->data['config']) && isset($this->data['list_name'])) {
            $information['list_name'] = $this->data['list_name'];
            $information['config'] = $this->data['config'];
        }

        if (Queue::getDefaultInstance()->hsetnx(Queue::queueInfoName(), $this->queue_name, json_encode($information, JSON_UNESCAPED_UNICODE))) {
            Queue::getDefaultInstance()->rpush(Queue::queueListName(), $this->queue_name);
        } else {
            return self::response(400, [], 'Queue already exists!');
        }
        return self::response(200, [], 'Queue created successfully!');
    }

    /**
     * 删除队列
     */
    public function dropQueue()
    {
        //删除队列定义
        Queue::getDefaultInstance()->hdel(Queue::queueInfoName(), $this->queue_name);
        Queue::getDefaultInstance()->lrem(Queue::queueListName(), 0, $this->queue_name);

        //删除消息体
        Queue::getDefaultInstance()->del(Queue::messageName($this->queue_name));

        //删除延时消息
        Queue::getDelayInstance()->del(Queue::delayName($this->queue_name));

        //删除已读消息
        Queue::getReadInstance()->del(Queue::readName($this->queue_name));

        //删除活跃消息
        Queue::getActiveInstance()->del(Queue::activeName($this->queue_name));

        return self::response(200, [], 'Queue deleted successfully!');
    }

    /**
     * 发送消息
     * @return bool|string
     * @throws \Exception
     */
    public function addMessage()
    {
        if (!isset($this->data['message']) || !is_string($this->data['message']) ) {
            return self::response(400, [], 'Message body error!');
        }

        $info = $this->getQueueInfo();

        if (isset($this->data['delay_time']) && $this->data['delay_time'] > 0) {
            $delay = $this->data['delay_time'];
        } else {
            $delay = $info['delay_time'];
        }

        if ($delay <= 0) {
            return self::response(400, [], 'Delay time less than 0!');
        }

        do {
            $messageId = $this->GenerateUid($this->queue_name);
        } while( ! Queue::getDefaultInstance()->hsetnx(Queue::messageName($this->queue_name), $messageId, $this->data['message']) );

        Queue::getDelayInstance()->zadd(Queue::delayName($this->queue_name), date('YmdHis', time() + $delay), $messageId);
        return self::response(200, ['messageId' => $messageId], 'Message sent successfully!');
    }

    /**
     * 获取消息
     * @return bool
     * @throws \Exception
     */
    public function getMessage()
    {
        $info = $this->getQueueInfo();

        if( isset($info['config']['host']) ) {
            $message = Redis::instance('', $info['config'])->lpop($info['list_name']);
            Redis::close('', $info['config']);
            return self::response(200, ['messageId'=>'custom-active-queue', 'content'=>$message]);
        }

        $messageId = Queue::getActiveInstance()->lpop(Queue::activeName($this->queue_name));
        if (! $messageId ) {
            return self::response(200, ['messageId'=>'', 'content'=>''], 'Message is empty!');
        }

        $message = Queue::getDefaultInstance()->hget(Queue::messageName($this->queue_name), $messageId);
        if (!$message) {
            return self::response(400, ['messageId'=>$messageId], 'Message body does not exist!');
        }

        Queue::getReadInstance()->zadd(Queue::readName($this->queue_name), time() + $info['hide_time'], $messageId);

        return self::response(200, ['messageId'=>$messageId, 'content'=>$message]);
    }

    /**
     * 删除消息
     * @return bool
     * @throws \Exception
     */
    public function deleteMessage()
    {
        if (!isset($this->data['messageId'])) {
            return self::response(400, [], 'messageId does not exist!');
        }

        //消息体
        Queue::getDefaultInstance()->hdel(Queue::messageName($this->queue_name), $this->data['messageId']);
        //延迟
        Queue::getDelayInstance()->zrem(Queue::delayName($this->queue_name), $this->data['messageId']);
        //已读
        Queue::getReadInstance()->zrem(Queue::readName($this->queue_name), $this->data['messageId']);
        //活跃
        Queue::getActiveInstance()->lrem(Queue::activeName($this->queue_name), 0, $this->data['messageId']);

        return self::response(200, [], 'Message deleted successfully!');
    }

    /**
     * 获取队列定义信息
     * @return array
     * @throws \Exception
     */
    private function getQueueInfo()
    {
        $info = Queue::getDefaultInstance()->hget(Queue::queueInfoName(), $this->queue_name);
        if ( !$info ) {
            throw new \Exception('Queue does not exist!');
        }
        return json_decode($info, true);
    }

    private function GenerateUid($salt = '')
    {
        return md5(uniqid(microtime(true).$salt.mt_rand(),true));
    }

    public static function response($status = 200, $data = [], $message = 'success')
    {
        $result = [
            'status'  => $status,
            'message' => $message,
            'data'    => $data
        ];
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }
}