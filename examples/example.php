<?php

require(__DIR__ . '/vendor/autoload.php');

use Ihsandevs\EdgeGpt\Chatbot;

$bot = new Chatbot(__DIR__ . '/cookies.json');

// You can use the following conversation styles: CREATIVE, BALANCED, PRECISE. Or you can keep it empty.
$response = $bot->ask('Buatkan kode php untuk menampilkan "Hello Ihsan Devs" pada terminal.', 'PRECISE');

echo $response->answer();