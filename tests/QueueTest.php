<?php

namespace Delay\Tests;

use Ruesin\Utils\Config;
use PHPUnit\Framework\TestCase;

class QueueTest extends TestCase
{
    public function request($operation, $queue_name, $data = [])
    {
        $client = new \GuzzleHttp\Client();
        $request = [
            'action' => $operation,
            'queue_name' => $queue_name,
        ];
        if (!empty($data)) {
            $request['data'] = $data;
        }
        $host = Config::get('server.http.host', '127.0.0.1');
        $port = Config::get('server.http.port', '9501');

        $response = $client->get("http://{$host}:{$port}?" . http_build_query($request));
        return json_decode($response->getBody(), true);
    }

    public function queueNameProvider()
    {
        return [[
            'config' => 'ruesin_' . time()
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
            'hid_time' => 10,
        ];
        $content = $this->request('create', $queueName, $data);
        $this->assertEquals('200', $content['status']);

        $content = $this->request('create', $queueName, $data);
        $this->assertEquals('400', $content['status']);
    }

    /**
     * @dataProvider queueNameProvider
     * @param string $queueName
     */
    public function testDrop($queueName)
    {
        $content = $this->request('drop', $queueName);
        $this->assertEquals('200', $content['status']);
    }
}