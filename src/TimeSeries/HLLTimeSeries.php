<?php

namespace App\TimeSeries;

use Predis\Client;

class HLLTimeSeries
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
            ],
            '1min' => [
                'name' => '1min',
                'ttl' => $this->units['day'] * 7,
                'duration' => $this->units['minute'],
            ],
            '1hour' => [
                'name' => '1hour',
                'ttl' => $this->units['day'] * 60,
                'duration' => $this->units['hour'],
            ],
            '1day' => [
                'name' => '1day',
                'ttl' => null,
                'duration' => $this->units['day'],
            ],
        ];
    }
    public function insert($timestampInSeconds, $thing)
    {
        foreach ($this->granualitites as $granuality) {
            $key = $this->getKeyName($granuality, $timestampInSeconds);

            $this->client->pfadd($key, $thing);

            if ($granuality['ttl'] !== null) {
                $this->client->expire($key, $granuality['ttl']);
            }
        }
    }

    public function getKeyName($granuality, $timestampInSeconds)
    {
        $roundedTimestamp = $this->getRoundedTimestamp($timestampInSeconds, $granuality['duration']);

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

            $multi->pfcount($key, $timestamp, $timestamp);
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