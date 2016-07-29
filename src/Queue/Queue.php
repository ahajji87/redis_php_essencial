<?php

namespace App\Queue;

class Queue
{
    protected $client;
    protected $name;
    protected $key;
    protected $timeout = 0;
    protected $size;

    public function __construct($name, $client)
    {
        $this->client = $client;
        $this->name = $name;
        $this->key = 'queues:'.$name;
    }

    /**
     * @return mixed
     */
    public function client()
    {
        return $this->client;
    }

    /**
     * @return mixed
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * @return int
     */
    public function timeout()
    {
        return $this->timeout;
    }
    /**
     * @return int
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }
    /**
     * @return mixed
     */
    public function size()
    {
        return $this->client->llen($this->key);
    }

    public function push($data)
    {
        $this->client->lpush($this->key, $data);
    }

    public function pop()
    {
        return $this->client->brpop($this->key, $this->timeout);
    }
}