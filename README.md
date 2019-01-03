# delay-server

基于Redis实现的延时队列服务，客户端通过API实现队列的创建、删除以及消息的发送、接收、删除操作。

服务端会启动守护进程，持续检测到期延时消息、已读超时消息，移动至活跃消息，供API获取消息使用。

为避免单节点性能问题，可单独配置队列定义、消息体、延时消息、活跃消息、已读消息的Redis服务节点。

## 依赖

- Redis > 3.2
- PHP > 7.0
- [Swoole Extension](http://pecl.php.net/package/swoole) > 1.9.5
- [predis](https://github.com/nrk/predis) = 1.1.1
- [swover](https://github.com/ruesin/swover) > 1.1.0

## 安装

```
$ composer create-project --prefer-dist ruesin/delay-server
```

## 配置

拷贝`./config/samples/*.php`到`./config/`，并按需修改：

- `queue.php` 队列定义
- `redis.php` Redis服务器配置
- `secrets.php` API请求验证签名的密钥对
- `server.php` 服务启动的配置文件，参考[swover](https://github.com/ruesin/swover)

关于`redis.php`配置：

默认情况下，队列定义、消息体使用`default`，延时消息使用`delay`，活跃消息使用`active`，已读消息使用`read`。

如果只有一个Redis服务，可以在`redis.php`拷贝定义相同配置。或者在`queue.php`中指定各消息key与redis.php中key的映射关系。

```
'redis_key' => [
    'default' => 'default',
    'delay'   => 'default',
    'active'  => 'default',
    'read'    => 'default'
]
```

## 使用

- Process 服务

  持续检测到期消息，将到期的延时消息、超时的已读消息移动到活跃消息队列。

```
$ php server.php process [start|stop|reload|restart]
```

- Sockets 服务

  接收客户端请求，实现创建队列、删除队列、发送延时消息、获取消息、删除消息的操作。可以使用`http`或`tcp`启动相应的swoole服务。

```
$ php server.php [http|tcp] [start|stop|reload|restart]
```

也可以选择使用 nginx + php-fpm，将网站目录指向`./public/`即可。

- 注意

  获取活跃消息后，需要删除消息，否则会从已读消息重新移动回活跃消息。

API请求详见[delay-client](https://github.com/ruesin/delay-client)



