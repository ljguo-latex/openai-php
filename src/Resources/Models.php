<?php

declare(strict_types=1);

namespace OpenAI\Resources;

use OpenAI\Client;
use OpenAI\Exceptions\ApiException;
use OpenAI\Responses\ModelListResponse;
use OpenAI\Responses\ModelResponse;

class Models
{
    public function __construct(private readonly Client $client) {}

    /**
     * @throws ApiException
     */
    public function list(): ModelListResponse
    {
        $data = $this->client->get('models');

        return ModelListResponse::fromArray($data);
    }

    /**
     * @throws ApiException
     */
    public function retrieve(string $modelId): ModelResponse
    {
        $data = $this->client->get("models/{$modelId}");

        return ModelResponse::fromArray($data);
    }
}
