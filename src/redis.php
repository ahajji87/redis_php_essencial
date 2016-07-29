<?php

require '../vendor/autoload.php';
Predis\Autoloader::register();

function client()
{
    return new Predis\Client(
        [
            'host' => '127.0.0.1',
            'port' => 6379,
        ],
        [
            'prefix' => 'php:'
        ]
    );
}

/*
$client->incr('string:counter');
$par = $client->mget(['stirng:atrapalo', 'string:counter']);
print_r($par);

$client->rpush('list:lista', 'item1', 'item2');
$par = $client->lpop('list:lista');
print_r($par);

$client->hset('set:books', 'title', 'redis bueno');
$par = $client->hgetall('set:books');
print_r($par);

$client->sadd('set:users', 'amine', 'hajji');
$par = $client->smembers('set:users');
print_r($par);

$client->zadd('sorted:programmers', 1940, 'amine hajji');
$client->zadd('sorted:programmers', 1912, 'alfonso manel');
$par = $client->zrange('sorted:programmers', 0, -1, 'withscores');

print_r($par);
*/