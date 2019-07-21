<?php
(function () {
    \Ruesin\Utils\Config::loadPath(ROOT_DIR.'/config/');

    foreach (\Ruesin\Utils\Config::get('redis') as $name=>$config) {
        \Ruesin\Utils\Redis::setConfig($name, $config);
    }

})();
