<?php

namespace App\TimeSeries;

use Predis\Client;

class SortedTimeSeries
{
    protected $units = [
        'second' =>  1,
        'minute' =>  60,
        'hour' =>  60 * 60,
        'day' =>  24 * 60 * 60
    ];
    protected $granualitites;
    protected $key;
    /** @var  Client */
    protected $client;
    protected $namespace;

    public function __construct($namespace, $client)
    {
        $this->namespace = $namespace;
        $this->client = $client;
        $this->granualitites();
    }

    public function granualitites()
    {
        $this->granualitites = [
            '1sec' => [
                'name' => '1sec',
                'ttl' => $this->units['hour'] * 2,
                'duration' => 1,
                'quantity' => $this->units['minute'] * 2,
            ],
            '1min' => [
                'name' => '1min',
                'ttl' => $this->units['day'] * 7,
                'duration' => $this->units['minute'],
                'quantity' => $this->units['hour'] * 2,
            ],
            '1hour' => [
                'name' => '1hour',
                'ttl' => $this->units['day'] * 60,
                'duration' => $this->units['hour'],
                'quantity' => $this->units['day'] * 5,
            ],
            '1day' => [
                'name' => '1sec',
                'ttl' => null,
                'duration' => $this->units['day'],
                'quantity' => $this->units['day'] * 30
            ],
        ];
    }
    public function insert($timestampInSeconds, $thing)
    {
        foreach ($this->granualitites as $granuality) {
            $key = $this->getKeyName($granuality, $timestampInSeconds);
            $timestampScore = $this->getRoundedTimestamp($timestampInSeconds, $granuality['duration']);
            $member = $timestampScore.':'.$thing;

            $this->client->zadd($key, $timestampScore, $member);

            if ($granuality['ttl'] !== null) {
                $this->client->expire($key, $granuality['ttl']);
            }
        }
    }

    public function getKeyName($granuality, $timestampInSeconds)
    {
        $roundedTimestamp = $this->getRoundedTimestamp($timestampInSeconds, $granuality['quantity']);

        return implode(':', [$this->namespace, $granuality['name'], $roundedTimestamp]);
    }

    public function getRoundedTimestamp($timestampInSeconds, $precision)
    {
        return floor($timestampInSeconds/$precision) * $precision;
    }

    public function fetch($granualityName, $begintimestamp, $endTimestamp, $onComplete)
    {
        $granuality = $this->granualitites[$granualityName];
        $begin = $this->getRoundedTimestamp($begintimestamp, $granuality['duration']);
        $end = $this->getRoundedTimestamp($endTimestamp, $granuality['duration']);

        $multi = $this->client->transaction()->multi();
        $results = [];

        for ($timestamp = $begin; $timestamp <= $end; $timestamp += $granuality['duration']) {
            $key = $this->getKeyName($granuality, $timestamp);

            $multi->zcount($key, $timestamp, $timestamp);
        }

        $data = $multi->exec();

        for ($i = 0; $i < count($data); $i++) {
            $timestamp = $begintimestamp + $i * $granuality['duration'];
            $value = null === $data[$i] ? 0 : (int) $data[$i];
            $results[] = [
                'timestamp' => $timestamp,
                'value' => $value,
            ];
        }

        $onComplete($granualityName, $results);
    }
}