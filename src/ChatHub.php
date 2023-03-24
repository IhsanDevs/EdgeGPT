<?php

namespace Ihsandevs\EdgeGpt;

use Composer\CaBundle\CaBundle;
use GuzzleHttp\Client as HttpClient;
use WebSocket\Client as WebSocketClient;

define("DELIMITER", "\x1e");

// Generate random IP between range 13.104.0.0/14
$FORWARDED_IP = "13." . rand(104, 107) . "." . rand(0, 255) . "." . rand(0, 255);

$HEADERS = [
    "accept"                      => "application/json",
    "accept-language"             => "en-US,en;q=0.9",
    "content-type"                => "application/json",
    "sec-ch-ua"                   => '"Not_A Brand";v="99", "Microsoft Edge";v="110", "Chromium";v="110"',
    "sec-ch-ua-arch"              => '"x86"',
    "sec-ch-ua-bitness"           => '"64"',
    "sec-ch-ua-full-version"      => '"109.0.1518.78"',
    "sec-ch-ua-full-version-list" => '"Chromium";v="110.0.5481.192", "Not A(Brand";v="24.0.0.0", "Microsoft Edge";v="110.0.1587.69"',
    "sec-ch-ua-mobile"            => "?0",
    "sec-ch-ua-model"             => "",
    "sec-ch-ua-platform"          => '"Windows"',
    "sec-ch-ua-platform-version"  => '"15.0.0"',
    "sec-fetch-dest"              => "empty",
    "sec-fetch-mode"              => "cors",
    "sec-fetch-site"              => "same-origin",
    "x-ms-client-request-id"      => com_create_guid(),
    "x-ms-useragent"              => "azsdk-js-api-client-factory/1.0.0-beta.1 core-rest-pipeline/1.10.0 OS/Win32",
    "Referer"                     => "https://www.bing.com/search?q=Bing+AI&showconv=1&FORM=hpcodx",
    "Referrer-Policy"             => "origin-when-cross-origin",
    "x-forwarded-for"             => $FORWARDED_IP,
];


class ChatHub
{
    private $wss;
    private $request;
    private $httpClient;

    public function __construct(Conversation $conversation)
    {
        $this->wss = null;
        $this->request = new ChatHubRequest(
            $conversation->getStruct()["conversationSignature"],
            $conversation->getStruct()["clientId"],
            $conversation->getStruct()["conversationId"]
        );

        $this->httpClient = new HttpClient();
    }

    public function askStream($prompt, $conversation_style = null)
    {
        global $HEADERS;
        if ($this->wss)
        {
            $this->wss->close();
        }

        $this->wss = new WebSocketClient("wss://sydney.bing.com/sydney/ChatHub", [
            'headers'     => $HEADERS,
            'timeout'     => 60,
            'ssl_context' => stream_context_create([
                'ssl' => [
                    'verify_peer'      => true,
                    'verify_peer_name' => true,
                    'cafile'           => CaBundle::getSystemCaRootBundlePath(),
                ],
            ]),
        ]);


        $this->initialHandshake();

        $this->request->update($prompt, $conversation_style);

        $this->wss->send(append_identifier($this->request->getStruct()));

        $final = false;
        while (! $final)
        {
            $objects = explode("\x1e", $this->wss->receive());

            foreach ($objects as $obj)
            {
                if ($obj === null || $obj === "")
                {
                    continue;
                }
                $response = json_decode($obj, true);
                if (isset($response["type"]) && $response["type"] === 1 && isset($response["arguments"][0]["messages"]))
                {
                    $resp_txt = $response["arguments"][0]["messages"][0]["adaptiveCards"][0]["body"][0]["text"] ?? null;
                    yield false => $resp_txt;
                }
                elseif (isset($response["type"]) && $response["type"] === 2)
                {
                    $final = true;
                    yield true => $response;
                }
            }
        }
    }

    private function initialHandshake()
    {
        $this->wss->send(append_identifier(["protocol" => "json", "version" => 1]));
        $this->wss->receive();
    }

    public function close()
    {
        if ($this->wss)
        {
            $this->wss->close();
        }
    }
}

function append_identifier($msg)
{
    return json_encode($msg) . DELIMITER;
}

function com_create_guid()
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );
}