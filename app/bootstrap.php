<?php
define('ROOT_DIR', dirname(__DIR__) . '/');

(function () {
    \Ruesin\Utils\Config::loadPath(ROOT_DIR . '/config/');

    \Ruesin\Utils\Redis::setConfig('default', \Ruesin\Utils\Config::get('redis'));
})();
