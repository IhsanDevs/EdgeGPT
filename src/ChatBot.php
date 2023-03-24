<?php

namespace Ihsandevs\EdgeGpt;

use Spatie\Async\Pool;

class Chatbot
{
    private $cookiePath;
    private $cookies;
    private $proxy;
    private $chatHub;

    public function __construct($cookiePath = "", $cookies = null, $proxy = null)
    {
        $this->cookiePath = $cookiePath;
        $this->cookies = $cookies;
        $this->proxy = $proxy;
        $this->chatHub = new ChatHub(
            new Conversation($this->cookiePath, $this->cookies, $this->proxy)
        );
    }

    public function ask($prompt, $conversation_style = null)
    {
        // check if length of prompt is more than 2000
        if (strlen($prompt) > 2000)
        {
            throw new \RuntimeException("The prompt is too long. The prompt may only be 2000 characters long.");
        }
        $response = null;
        // Set the maximum output buffer size to 100MB
        $maxOutputBufferSize = 1024 * 1024 * 100;

        $pool = Pool::create();

        $pool->add(function () use ($prompt, $conversation_style)
        {
            foreach ($this->chatHub->askStream($prompt, $conversation_style) as $final => $data)
            {
                if ($final)
                {
                    return $data;
                }
            }
            $this->chatHub->close();
        })->then(function ($data) use (&$response, $maxOutputBufferSize)
        {
            $serializedData = serialize($data);

            if (strlen($serializedData) > $maxOutputBufferSize)
            {
                throw new \RuntimeException("The output is too large. The serialized output may only be {$maxOutputBufferSize} bytes long.");
            }

            $response = new Response(unserialize($serializedData));
        });

        $pool->wait();

        return $response;
    }



    public function askStream($prompt, $conversation_style = null)
    {
        return $this->chatHub->askStream($prompt, $conversation_style);
    }

    public function close()
    {
        $pool = Pool::create();

        $pool->add(function ()
        {
            $this->chatHub->close();
        });

        $pool->wait();
    }

    public function reset()
    {
        $pool = Pool::create();

        $pool->add(function ()
        {
            $this->chatHub->close();
            $this->chatHub = new ChatHub(new Conversation($this->cookiePath, $this->cookies));
        });

        $pool->wait();
    }
}