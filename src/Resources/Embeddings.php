<?php

declare(strict_types=1);

namespace Ljguo\OpenAI\Resources;

use Ljguo\OpenAI\Client;
use Ljguo\OpenAI\Exceptions\ApiException;
use Ljguo\OpenAI\Responses\EmbeddingResponse;

class Embeddings
{
    public function __construct(private readonly Client $client) {}

    /**
     * @param  string|string[]  $input
     * @throws ApiException
     */
    public function create(
        string|array $input,
        ?string $model = null,
        array $options = []
    ): EmbeddingResponse {
        $payload = array_merge([
            'model' => $model ?? $this->client->getDefaultModel() ?? 'text-embedding-3-small',
            'input' => $input,
        ], $options);

        $data = $this->client->post('embeddings', $payload);

        return EmbeddingResponse::fromArray($data);
    }
}
