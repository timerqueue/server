<?php
define('ROOT_DIR', dirname(__DIR__) . '/');

require_once ROOT_DIR . 'vendor/autoload.php';

$data = [
    'get' => $_GET,
    'post' => $_POST,
    'input' => file_get_contents("php://input"),
    // 'header' => [], //TODO
    'server' => $_SERVER,
    'cookie' => $_COOKIE
];
$request = new \Swover\Utils\Request($data);

if (!$request->get('action')) {
    echo "What do you want?";
    return;
}

if (\App\Utils\Sign::verify($request) !== true) {
    echo 'no no no~';
    return;
}

echo \App\App::http($request);
