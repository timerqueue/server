<?php

namespace App;

class Sockets
{
    /**
     * 获取消息
     * ['action'=>'create', 'data' => ['delay'=> 11, 'message' => 'asdf', 'handleId'=>'aaaa'] ]
     */
    public static function run($request) //TODO request 对象
    {
        try {
            if (!isset($request['action']) || !isset($request['queue_name'])) {
                return Handle::response(400, [], 'param error!');
            }

            $class = self::route($request['action']);
            if ($class == false) {
                return Handle::response(404, [], 'action error!');
            }

            if (isset($request['data']) && is_string($request['data'])) {
                $request['data'] = json_decode($request['data'], true);
            }

            return call_user_func([new $class($request), 'handle']);
        } catch (\Exception $e) {
            return Handle::response(500, [], $e->getMessage());
        }
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