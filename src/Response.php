<?php

namespace Ihsandevs\EdgeGpt;

class Response
{
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getMessages(): array
    {
        return $this->data['item']['messages'] ?? [];
    }

    public function getFirstNewMessageIndex(): ?int
    {
        return $this->data['item']['firstNewMessageIndex'] ?? null;
    }

    public function getConversationId(): ?string
    {
        return $this->data['item']['conversationId'] ?? null;
    }

    public function getRequestId(): ?string
    {
        return $this->data['item']['requestId'] ?? null;
    }

    public function getConversationExpiryTime(): ?string
    {
        return $this->data['item']['conversationExpiryTime'] ?? null;
    }

    public function getTelemetry(): ?array
    {
        return $this->data['item']['telemetry'] ?? null;
    }

    public function getThrottling(): ?array
    {
        return $this->data['item']['throttling'] ?? null;
    }

    public function getResult(): ?array
    {
        return $this->data['item']['result'] ?? null;
    }

    public function getSuggestedResponses(): array
    {
        $suggestedResponses = [];
        $messages = $this->getMessages();
        foreach ($messages as $message)
        {
            if (isset($message['suggestedResponses']))
            {
                $suggestedResponses = array_merge($suggestedResponses, $message['suggestedResponses']);
            }
        }

        return $suggestedResponses;
    }

    public function answer(): ?string
    {
        $messages = $this->getMessages();
        $latestBotResponse = null;

        foreach ($messages as $message)
        {
            if ($message['author'] === 'bot')
            {
                $latestBotResponse = $message['text'];
            }
        }

        return $latestBotResponse;
    }

}