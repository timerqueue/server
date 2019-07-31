<?php

namespace App;

use App\Queue\Base;
use App\Utils\Connection;
use App\Utils\Queue;

class App
{
    /**
     * ['action'=>'create', 'data' => ['delay'=> 11, 'message' => 'asdf', 'handleId'=>'aaaa'] ]
     *
     * @param \Swover\Utils\Request $request
     * @return mixed
     */
    public static function http(\Swover\Utils\Request $request)
    {
        try {
            if (!$request->get('action')
                || !$request->get('queue_name')) {
                return Base::response(400, [], 'param error!');
            }

            $class = self::route($request->get('action'));
            if ($class == false) {
                return Base::response(404, [], 'action error!');
            }

            return call_user_func([new $class($request->get()), 'handle']);
        } catch (\Exception $e) {
            return Base::response(500, [], $e->getMessage());
        }
    }

    public static function process()
    {
        sleep(1);
        $result = true;
        $instance = Connection::default();
        $queueName = $instance->lpop(Queue::queueListName());
        if (!$queueName) return $result;

        try {
            $queueInfo = $instance->hget(Queue::queueInfoName(), $queueName);
            if (!$queueInfo) {
                $class = self::route('drop');
                call_user_func([new $class(['queue_name' => $queueName]), 'handle']);
                return $result;
            }

            Queue::active($queueName, $queueInfo);
        } catch (\Exception $e) {
            $result = false;
        }

        if ($instance->hexists(Queue::queueInfoName(), $queueName)) {
            $instance->rpush(Queue::queueListName(), $queueName);
        }
        return $result;
    }

    /**
     * @param $action
     * @return bool | \App\Queue\Base
     */
    private static function route($action)
    {
        $maps = [
            'create' => \App\Queue\Create::class,
            'drop' => \App\Queue\Drop::class,
            'add' => \App\Queue\Insert::class,
            'get' => \App\Queue\Select::class,
            'del' => \App\Queue\Delete::class,
            'clear' => \App\Queue\Truncate::class,
        ];
        return isset($maps[$action]) ? $maps[$action] : false;
    }
}