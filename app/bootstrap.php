<?php
define('ROOT_DIR', dirname(__DIR__) . '/');

(function () {
    \Ruesin\Utils\Config::loadPath(ROOT_DIR . '/config/');

    foreach (\Ruesin\Utils\Config::get('redis') as $name => $config) {
        \Ruesin\Utils\Redis::setConfig($name, $config);
    }
})();
