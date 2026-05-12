<?php

declare(strict_types=1);

namespace OpenAI\Resources;

use OpenAI\Client;
use OpenAI\Exceptions\ApiException;
use OpenAI\Responses\ChatResponse;

class Chat
{
    private array $messages = [];
    private ?string $model = null;
    private float $temperature = 1.0;
    private ?int $maxTokens = null;
    private array $options = [];

    public function __construct(private readonly Client $client) {}

    public function model(string $model): static
    {
        $this->model = $model;
        return $this;
    }

    public function temperature(float $temperature): static
    {
        $this->temperature = $temperature;
        return $this;
    }

    public function maxTokens(int $maxTokens): static
    {
        $this->maxTokens = $maxTokens;
        return $this;
    }

    public function system(string $content): static
    {
        $this->messages[] = ['role' => 'system', 'content' => $content];
        return $this;
    }

    public function user(string $content): static
    {
        $this->messages[] = ['role' => 'user', 'content' => $content];
        return $this;
    }

    public function assistant(string $content): static
    {
        $this->messages[] = ['role' => 'assistant', 'content' => $content];
        return $this;
    }

    /** Alias for user() */
    public function message(string $content): static
    {
        return $this->user($content);
    }

    public function withMessages(array $messages): static
    {
        $this->messages = $messages;
        return $this;
    }

    public function option(string $key, mixed $value): static
    {
        $this->options[$key] = $value;
        return $this;
    }

    /**
     * @throws ApiException
     */
    public function send(): ChatResponse
    {
        $data = $this->client->post('chat/completions', $this->buildPayload());
        return ChatResponse::fromArray($data);
    }

    /**
     * @throws ApiException
     */
    public function stream(callable $callback): string
    {
        $full = '';
        $this->client->stream('chat/completions', $this->buildPayload(), function (string $chunk) use (&$full, $callback) {
            $full .= $chunk;
            $callback($chunk);
        });
        return $full;
    }

    private function buildPayload(): array
    {
        $payload = array_merge([
            'model'       => $this->model ?? $this->client->getDefaultModel() ?? 'gpt-4o',
            'messages'    => $this->messages,
            'temperature' => $this->temperature,
        ], $this->options);

        if ($this->maxTokens !== null) {
            $payload['max_tokens'] = $this->maxTokens;
        }

        return $payload;
    }
}
