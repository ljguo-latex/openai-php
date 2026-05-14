<?php

declare(strict_types=1);

namespace OpenAI\Resources;

use OpenAI\Client;
use OpenAI\Exceptions\ApiException;
use OpenAI\Responses\CompletionResponse;
use OpenAI\Responses\UsageResponse;

class Completions
{
    private string $prompt = '';
    private ?string $model = null;
    private float $temperature = 1.0;
    private ?int $maxTokens = null;
    private array $options = [];

    public function __construct(private readonly Client $client) {}

    public function prompt(string $prompt): static
    {
        $this->prompt = $prompt;
        return $this;
    }

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

    public function option(string $key, mixed $value): static
    {
        $this->options[$key] = $value;
        return $this;
    }

    /**
     * @throws ApiException
     */
    public function send(): CompletionResponse
    {
        $data = $this->client->post('completions', $this->buildPayload());
        return CompletionResponse::fromArray($data);
    }

    /**
     * @throws ApiException
     */
    public function stream(callable $callback): string
    {
        $full = '';
        $this->client->stream('completions', $this->buildPayload(), function (string $chunk, ?UsageResponse $usage = null) use (&$full, $callback) {
            if ($usage !== null) {
                $callback($chunk, $usage);
                return;
            }

            $full .= $chunk;
            $callback($chunk, null);
        });
        return $full;
    }

    private function buildPayload(): array
    {
        $payload = array_merge([
            'model'       => $this->model ?? $this->client->getDefaultModel() ?? 'gpt-3.5-turbo-instruct',
            'prompt'      => $this->prompt,
            'temperature' => $this->temperature,
        ], $this->options);

        if ($this->maxTokens !== null) {
            $payload['max_tokens'] = $this->maxTokens;
        }

        return $payload;
    }
}
