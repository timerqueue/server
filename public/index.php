<?php
define('ROOT_DIR', dirname(__DIR__).'/');

require_once ROOT_DIR.'vendor/autoload.php';

$request = array_merge($_GET, $_POST);

if (!isset($request['action']) || !$request['action']) {
    echo "What do you want?";
    return;
}

if (\App\Utils\Sign::verify($request) !== true) {
    echo 'no no no~';
    return;
}

echo \App\Sockets::run($request);
