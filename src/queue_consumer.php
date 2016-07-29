<?php

require "redis.php";

$queue = new \App\Queue\Queue('logs', client());

function logMessage($queue)
{
    $log = $queue->pop();

    $name = $log[0];
    $message = $log[1];

    echo "[consumer] Got log: " . $message." ";

    echo $queue->size() . " logs left!\n";

    logMessage($queue);
}

logMessage($queue);