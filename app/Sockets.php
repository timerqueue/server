<?php

namespace App;

class Sockets
{
    private static $routes = [
        'create' => 'createQueue',
        'drop' => 'dropQueue',
        'add' => 'addMessage',
        'get' => 'getMessage',
        'del' => 'deleteMessage',
        //'clear' //todo
    ];

    /**
     * 获取消息
     * ['action'=>'create', 'data' => ['delay'=> 11, 'message' => 'asdf', 'handleId'=>'aaaa'] ]
     */
    public static function run($request)
    {
        try {
            if (!isset($request['action']) || !isset($request['queue_name'])) {
                return Handle::response(404, [], 'param error!');
            }

            if (!isset(self::$routes[$request['action']])) {
                return Handle::response(404, [], 'action error!');
            }

            if (isset($request['data']) && is_string($request['data'])) {
                $request['data'] = json_decode($request['data'], true);
            }

            $instance = new Handle($request);
            return call_user_func([$instance, self::$routes[$request['action']]]);
        } catch (\Exception $e) {
            return Handle::response(400, [], $e->getMessage());
        }
    }
}