<?php

require "redis.php";

$queue = new \App\Queue\Queue('logs', client());

for ($i = 1; $i <= 5; $i++) {
    $queue->push("Log numero ".$i);
}

echo 'Created 5 logs';