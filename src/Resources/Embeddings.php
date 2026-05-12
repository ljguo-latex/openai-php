<?php

declare(strict_types=1);

namespace OpenAI\Resources;

use OpenAI\Client;
use OpenAI\Exceptions\ApiException;
use OpenAI\Responses\EmbeddingResponse;

class Embeddings
{
    private string|array $input = '';
    private ?string $model = null;
    private array $options = [];

    public function __construct(private readonly Client $client) {}

    public function input(string|array $input): static
    {
        $this->input = $input;
        return $this;
    }

    public function model(string $model): static
    {
        $this->model = $model;
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
    public function send(): EmbeddingResponse
    {
        $payload = array_merge([
            'model' => $this->model ?? $this->client->getDefaultModel() ?? 'text-embedding-3-small',
            'input' => $this->input,
        ], $this->options);

        $data = $this->client->post('embeddings', $payload);
        return EmbeddingResponse::fromArray($data);
    }
}
