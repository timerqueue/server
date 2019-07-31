<?php

namespace Delay\Tests;

use PHPUnit\Framework\TestCase;
use Swover\Utils\Request;

class QueueTest extends TestCase
{
    /**
     * @param $action
     * @param $queue_name
     * @param array $data
     * @return Request
     */
    public function buildRequest($action, $queue_name, $data = [])
    {
        $request = [
            'action' => $action,
            'queue_name' => $queue_name,
            'data' => $data
        ];
        return new Request(['get' => $request]);
    }

    public function callHttp($action, $queue_name, $data = [])
    {
        $request = $this->buildRequest($action, $queue_name, $data);
        $json = \App\App::http($request);
        return json_decode($json, true);
    }

    public function queueNameProvider()
    {
        return [[
            'config' => 'test_delay_server_' . date('YmdHis')
        ]];
    }

    /**
     * @dataProvider queueNameProvider
     * @param string $queueName
     */
    public function testCreate($queueName)
    {
        $data = [
            'delay_time' => 10,
            'hide_time' => 10,
        ];
        $content = $this->callHttp('create', $queueName, $data);
        $this->assertEquals('200', $content['status']);

        $content = $this->callHttp('create', $queueName, $data);
        $this->assertEquals('400', $content['status']);
    }

    /**
     * @dataProvider queueNameProvider
     * @param string $queueName
     */
    public function testDrop($queueName)
    {
        $content = $this->callHttp('drop', $queueName);
        $this->assertEquals('200', $content['status']);
    }
}