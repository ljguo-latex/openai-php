<?php

declare(strict_types=1);

namespace OpenAI\Resources;

use OpenAI\Client;
use OpenAI\Exceptions\ApiException;
use OpenAI\Responses\ChatResponse;

class Chat
{
    public function __construct(private readonly Client $client) {}

    /**
     * Send a chat completion request.
     *
     * @param  array<array{role: string, content: string}>  $messages
     * @throws ApiException
     */
    public function create(
        array $messages,
        ?string $model = null,
        float $temperature = 1.0,
        ?int $maxTokens = null,
        array $options = []
    ): ChatResponse {
        $payload = array_merge([
            'model'       => $model ?? $this->client->getDefaultModel() ?? 'gpt-4o',
            'messages'    => $messages,
            'temperature' => $temperature,
        ], $options);

        if ($maxTokens !== null) {
            $payload['max_tokens'] = $maxTokens;
        }

        $data = $this->client->post('chat/completions', $payload);

        return ChatResponse::fromArray($data);
    }

    /**
     * Convenience method: send a single user message.
     *
     * @throws ApiException
     */
    public function message(
        string $content,
        ?string $model = null,
        float $temperature = 1.0,
        ?int $maxTokens = null,
        array $options = []
    ): ChatResponse {
        return $this->create(
            [['role' => 'user', 'content' => $content]],
            $model,
            $temperature,
            $maxTokens,
            $options
        );
    }
}
