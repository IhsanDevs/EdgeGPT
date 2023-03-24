<?php

namespace Ihsandevs\EdgeGpt;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

$HEADERS_INIT_CONVER = [
    "authority"                   => "edgeservices.bing.com",
    "accept"                      => "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
    "accept-language"             => "en-US,en;q=0.9",
    "cache-control"               => "max-age=0",
    "sec-ch-ua"                   => '"Chromium";v="110", "Not A(Brand";v="24", "Microsoft Edge";v="110"',
    "sec-ch-ua-arch"              => '"x86"',
    "sec-ch-ua-bitness"           => '"64"',
    "sec-ch-ua-full-version"      => '"110.0.1587.69"',
    "sec-ch-ua-full-version-list" => '"Chromium";v="110.0.5481.192", "Not A(Brand";v="24.0.0.0", "Microsoft Edge";v="110.0.1587.69"',
    "sec-ch-ua-mobile"            => "?0",
    "sec-ch-ua-model"             => '""',
    "sec-ch-ua-platform"          => '"Windows"',
    "sec-ch-ua-platform-version"  => '"15.0.0"',
    "sec-fetch-dest"              => "document",
    "sec-fetch-mode"              => "navigate",
    "sec-fetch-site"              => "none",
    "sec-fetch-user"              => "?1",
    "upgrade-insecure-requests"   => "1",
    "user-agent"                  => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36 Edg/110.0.1587.69",
    "x-edge-shopping-flag"        => "1",
];


class Conversation
{
    private $struct;
    private $session;

    public function __construct($cookiePath = "", $cookies = null, $proxy = null)
    {
        global $HEADERS_INIT_CONVER;
        $this->struct = array(
            "conversationId"        => null,
            "clientId"              => null,
            "conversationSignature" => null,
            "result"                => array("value" => "Success", "message" => null),
        );

        if ($cookies !== null)
        {
            $cookie_file = $cookies;
        }
        else
        {
            $cookiePath = $cookiePath ?: getenv("COOKIE_FILE");
            $cookie_file = json_decode(file_get_contents($cookiePath), true);
        }

        $cookieJar = new CookieJar();
        foreach ($cookie_file as $cookie)
        {
            $cookieJar->setCookie(new \GuzzleHttp\Cookie\SetCookie([
                'Name'   => $cookie['name'],
                'Value'  => $cookie['value'],
                'Domain' => 'edgeservices.bing.com'
            ]));
        }

        $this->session = new Client([
            'headers' => $GLOBALS['HEADERS_INIT_CONVER'],
            'timeout' => 30,
            'proxy'   => $proxy,
            'cookies' => $cookieJar,
        ]);

        // Send GET request
        $response = $this->session->get(
            getenv("BING_PROXY_URL") ?: "https://edgeservices.bing.com/edgesvc/turing/conversation/create"
        );

        if ($response->getStatusCode() != 200)
        {
            echo "Status code: " . $response->getStatusCode() . PHP_EOL;
            echo $response->getBody() . PHP_EOL;
            // echo $response->getEffectiveUrl() . PHP_EOL;
            throw new \Exception("Authentication failed");
        }

        try
        {
            $this->struct = json_decode($response->getBody(), true);
            if ($this->struct["result"]["value"] == "UnauthorizedRequest")
            {
                throw new NotAllowedToAccess($this->struct["result"]["message"]);
            }
        }
        catch (\Exception $exc)
        {
            throw new \Exception("Authentication failed. You have not been accepted into the beta.", 0, $exc);
        }
    }

    public function getStruct()
    {
        return $this->struct;
    }
}