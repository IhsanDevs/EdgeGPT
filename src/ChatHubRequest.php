<?php

namespace Ihsandevs\EdgeGpt;

class ChatHubRequest
{
    private $struct;
    private $clientId;
    private $conversationId;
    private $conversationSignature;
    private $invocationId;

    public function __construct($conversationSignature, $clientId, $conversationId, $invocationId = 0)
    {
        $this->struct = [];

        $this->clientId = $clientId;
        $this->conversationId = $conversationId;
        $this->conversationSignature = $conversationSignature;
        $this->invocationId = $invocationId;
    }

    public function getStruct(): array
    {
        return $this->struct;
    }

    public function update($prompt, $conversationStyle = null, $options = null)
    {
        if ($options === null)
        {
            $options = [
                "deepleo",
                "enable_debug_commands",
                "disable_emoji_spoken_text",
                "enablemm",
            ];
        }
        if ($conversationStyle !== null)
        {
            // Assuming you have a ConversationStyle class with appropriate values
            if (! ($conversationStyle instanceof ConversationStyle))
            {
                $conversationStyle = ConversationStyle::$conversationStyle();
            }
            $options = [
                "deepleo",
                "enable_debug_commands",
                "disable_emoji_spoken_text",
                "enablemm",
                $conversationStyle->value,
            ];
        }
        $this->struct = [
            "arguments"    => [
                [
                    "source"                => "cib",
                    "optionsSets"           => $options,
                    "isStartOfSession"      => $this->invocationId == 0,
                    "message"               => [
                        "author"      => "user",
                        "inputMethod" => "Keyboard",
                        "text"        => $prompt,
                        "messageType" => "Chat",
                    ],
                    "conversationSignature" => $this->conversationSignature,
                    "participant"           => [
                        "id" => $this->clientId,
                    ],
                    "conversationId"        => $this->conversationId,
                ],
            ],
            "invocationId" => (string) $this->invocationId,
            "target"       => "chat",
            "type"         => 4,
        ];
        $this->invocationId++;
    }
}