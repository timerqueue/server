<?php
namespace App\Utils;

class Sign
{
    public static function build($request, $time, $secret)
    {
        foreach ($request as $key => $val) {
            if (is_array($val)) {
                ksort($val);
                $request[$key] = json_encode($val, JSON_UNESCAPED_UNICODE);
            }
        }
        ksort($request);

        return md5(date('YmdHis', $time) . substr(md5(json_encode($request, JSON_UNESCAPED_UNICODE) . $time), 8, 16) . $secret);
    }

    public static function verify($request)
    {
        if (!isset($request['access_id']) || !$request['access_id']) return false;

        if (!isset($request['sign']) || !isset($request['time'])) return false;
        $sign = $request['sign'];
        $time = $request['time'];

        if (time() - $time > 100) return false;

        $secret = self::secret($request['access_id']);
        if (!$secret) return false;

        unset($request['sign'], $request['time'], $request['access_id']);

        $verify = self::build($request, $time, $secret);

        if ($verify === $sign) return true;

        return false;
    }

    private static function secret($secretId)
    {
        $secrets = \Swover\Utils\Config::get('secrets');
        return isset($secrets[$secretId]) ? $secrets[$secretId] : false;
    }
}