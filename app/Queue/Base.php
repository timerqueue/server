<?php

namespace App\Queue;

use App\Utils\Queue;

abstract class Base
{
    protected $queue_name = '';

    protected $data = [];

    protected $defaultInstance = null;

    public function __construct($request)
    {
        $this->queue_name = $request['queue_name'];
        $this->data = $request['data'] ?? [];

        $this->defaultInstance = Queue::getDefaultInstance();
    }

    abstract function handle();

    /**
     * 获取队列定义信息
     * @return array
     */
    protected function getQueueInfo()
    {
        $info = Queue::getDefaultInstance()->hget(Queue::queueInfoName(), $this->queue_name);
        return $info ? json_decode($info, true): [];
    }

    public static function response($status = 200, $data = [], $message = 'success') //TODO
    {
        $result = [
            'status' => $status,
            'message' => $message,
            'data' => $data
        ];
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }
}