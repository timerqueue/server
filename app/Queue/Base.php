<?php

namespace App\Queue;

use App\Utils\Connection;
use Ruesin\Utils\Config;

abstract class Base
{
    protected $data = [];

    protected $queue_name = '';

    protected $messageName = '';

    protected $delayName = '';

    protected $activeName = '';

    protected $readName = '';

    protected $defaultInstance = null;

    public function __construct($request)
    {
        $this->queue_name = $request['queue_name'];
        $this->data = $request['data'] ?? [];

        $queueConfig = Config::get('queue');

        $this->messageName = $this->queue_name . $queueConfig['message_suffix'];
        $this->delayName = $this->queue_name . $queueConfig['delay_suffix'];
        $this->activeName = $this->queue_name . $queueConfig['active_suffix'];
        $this->readName = $this->queue_name . $queueConfig['read_suffix'];

        $this->defaultInstance = Connection::default();
    }

    abstract function handle();

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