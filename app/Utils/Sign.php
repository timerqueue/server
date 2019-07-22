<?php

namespace App\Utils;

use Ruesin\Utils\Config;

class Sign
{
    public static function build($data, $time, $secret)
    {
        foreach ($data as $key => $val) {
            if (is_array($val)) {
                ksort($val);
                $data[$key] = json_encode($val, JSON_UNESCAPED_UNICODE);
            }
        }
        ksort($data);

        return md5(date('YmdHis', $time) . substr(md5(json_encode($data, JSON_UNESCAPED_UNICODE) . $time), 8, 16) . $secret);
    }

    public static function verify(\Swover\Utils\Request $request)
    {
        if (!$request->get('access_id')
            || !$request->get('sign')
            || !$request->get('time')) return false;

        $time = $request->get('time');
        if (time() - $time > 100) return false;

        $secret = Config::get('secrets.' . $request->get('access_id'));
        if (!$secret) return false;

        $data = $request->get(); //TODO
        unset($data['sign'], $data['time'], $data['access_id']);

        $verify = self::build($data, $time, $secret);

        if ($verify === $request->get('sign')) return true;

        return false;
    }
}