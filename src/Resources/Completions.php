<?php

declare(strict_types=1);

namespace OpenAI\Resources;

use OpenAI\Client;
use OpenAI\Exceptions\ApiException;
use OpenAI\Responses\CompletionResponse;

class Completions
{
    public function __construct(private readonly Client $client) {}

    /**
     * @throws ApiException
     */
    public function create(
        string $prompt,
        ?string $model = null,
        float $temperature = 1.0,
        ?int $maxTokens = null,
        array $options = []
    ): CompletionResponse {
        $payload = array_merge([
            'model'       => $model ?? $this->client->getDefaultModel() ?? 'gpt-3.5-turbo-instruct',
            'prompt'      => $prompt,
            'temperature' => $temperature,
        ], $options);

        if ($maxTokens !== null) {
            $payload['max_tokens'] = $maxTokens;
        }

        $data = $this->client->post('completions', $payload);

        return CompletionResponse::fromArray($data);
    }
}
