<?php

require "redis.php";

if (count($argv) < 2) {
    echo "ERROR: Youn need to spicify a data type! \n";
    echo "$ node using-timeseries.php [string|hash] \n";
    exit;
}

$dataType = $argv[1];

client()->flushall();

$time_start = microtime(true);


$types =  [
    'unique' => '\App\TimeSeries\SortedTimeSeries',
    'hll' => '\App\TimeSeries\HLLTimeSeries',
];

$concurrentPlays = new $types[$dataType]("concurrentPlays", client());
$beginTimestamp = 0;

$concurrentPlays->insert($beginTimestamp, "user:max");
$concurrentPlays->insert($beginTimestamp, "user:max");
$concurrentPlays->insert($beginTimestamp + 1, "user:hugo");
$concurrentPlays->insert($beginTimestamp + 1, "user:renata");
$concurrentPlays->insert($beginTimestamp + 3, "user:hugo");
$concurrentPlays->insert($beginTimestamp + 61, "user:kc");
$concurrentPlays->insert($beginTimestamp, "user:max");
$concurrentPlays->insert($beginTimestamp, "user:max");
$concurrentPlays->insert($beginTimestamp + 2, "user:hugo");
$concurrentPlays->insert($beginTimestamp + 2, "user:renata");
$concurrentPlays->insert($beginTimestamp + 6, "user:hugo");
$concurrentPlays->insert($beginTimestamp + 91, "user:kc");
$concurrentPlays->insert($beginTimestamp + 119, "user:max");
$concurrentPlays->insert($beginTimestamp + 3601, "user:max");
$concurrentPlays->insert($beginTimestamp + 4, "user:hugo");
$concurrentPlays->insert($beginTimestamp + 4, "user:renata");
$concurrentPlays->insert($beginTimestamp + 9, "user:hugo");
$concurrentPlays->insert($beginTimestamp + 121, "user:kc");
$concurrentPlays->insert($beginTimestamp + 3, "user:max");
$concurrentPlays->insert($beginTimestamp + 7300, "user:max");
$concurrentPlays->insert($beginTimestamp + 10, "user:hugo");
$concurrentPlays->insert($beginTimestamp + 9, "user:renata");
$concurrentPlays->insert($beginTimestamp + 32, "user:hugo");
$concurrentPlays->insert($beginTimestamp + 64, "user:kc");

function displayResults($granularityName, $results)
{
    echo "Results from ".$granularityName.":";
    echo "Timestamp \t| Value \n";
    echo "----------------- | ------- \n";
    for ($i = 0; $i < count($results); $i++) {
        echo "\t".$results[$i]['timestamp']."\t|".$results[$i]['value']."\n";
    }
}

$concurrentPlays->fetch("1sec", $beginTimestamp, $beginTimestamp + 4, 'displayResults');
$concurrentPlays->fetch("1min", $beginTimestamp, $beginTimestamp + 120, 'displayResults');
$concurrentPlays->fetch("1hour", $beginTimestamp, $beginTimestamp + 10000, 'displayResults');

client()->quit();

echo "\n benchmark: ".number_format(microtime(true) - $time_start, 3). "\n";

