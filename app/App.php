<?php

namespace App;

use App\Queue\Base;
use App\Queue\Timeout;
use App\Queue\Wakeup;
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
        $name = $instance->lpop(Queue::queueListName());
        if (!$name) return $result;

        try {
            $info = $instance->hget(Queue::queueInfoName(), $name);
            if (!$info) {
                $class = self::route('drop');
                call_user_func([new $class(['queue_name' => $name]), 'handle']);
                return $result;
            }

            $request = [
                'queue_name' => $name,
                'data' => [
                    'info' => json_decode($info, true),
                    'score' => date('YmdHis')
                ]
            ];

            (new Wakeup($request))->handle();
            (new Timeout($request))->handle();

        } catch (\Exception $e) {
            $result = false;
        }

        if ($instance->hexists(Queue::queueInfoName(), $name)) {
            $instance->rpush(Queue::queueListName(), $name);
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