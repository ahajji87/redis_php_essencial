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
    'string' => '\App\TimeSeries\StringTimeSeries',
    'hash' => '\App\TimeSeries\HashTimeSeries',
];

$item1Purchases = new $types[$dataType]("purchases:item1", client());
$beginTimestamp = 0;

$item1Purchases->insert($beginTimestamp);
$item1Purchases->insert($beginTimestamp + 1);
$item1Purchases->insert($beginTimestamp + 1);
$item1Purchases->insert($beginTimestamp + 3);
$item1Purchases->insert($beginTimestamp + 61);
$item1Purchases->insert($beginTimestamp + 80);
$item1Purchases->insert($beginTimestamp + 82);
$item1Purchases->insert($beginTimestamp + 119);
$item1Purchases->insert($beginTimestamp + 3603);
$item1Purchases->insert($beginTimestamp + 3345);
$item1Purchases->insert($beginTimestamp + 6166);
$item1Purchases->insert($beginTimestamp + 8088);
$item1Purchases->insert($beginTimestamp + 3601);
$item1Purchases->insert($beginTimestamp + 14000);
$item1Purchases->insert($beginTimestamp + 1400);
$item1Purchases->insert($beginTimestamp + 2402);
$item1Purchases->insert($beginTimestamp + 23);
$item1Purchases->insert($beginTimestamp + 96);
$item1Purchases->insert($beginTimestamp + 103);
$item1Purchases->insert($beginTimestamp + 13);
$item1Purchases->insert($beginTimestamp + 111);
$item1Purchases->insert($beginTimestamp + 36);
$item1Purchases->insert($beginTimestamp + 67);
$item1Purchases->insert($beginTimestamp + 84);
$item1Purchases->insert($beginTimestamp + 3605);

function displayResults($granularityName, $results)
{
    echo "Results from ".$granularityName.":";
    echo "Timestamp \t| Value \n";
    echo "----------------- | ------- \n";
    for ($i = 0; $i < count($results); $i++) {
        echo "\t".$results[$i]['timestamp']."\t|".$results[$i]['value']."\n";
    }
}

$item1Purchases->fetch("1sec", $beginTimestamp, $beginTimestamp + 10, 'displayResults');
$item1Purchases->fetch("1min", $beginTimestamp, $beginTimestamp + 200, 'displayResults');
$item1Purchases->fetch("1hour", $beginTimestamp, $beginTimestamp + 15000, 'displayResults');

client()->quit();

echo "\n benchmark: ".number_format(microtime(true) - $time_start, 3). "\n";
