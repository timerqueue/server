<?php

namespace App;

use App\Queue\Base;

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

            return call_user_func([new $class($request), 'handle']);
        } catch (\Exception $e) {
            return Base::response(500, [], $e->getMessage());
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