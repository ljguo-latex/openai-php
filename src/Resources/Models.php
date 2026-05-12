<?php

declare(strict_types=1);

namespace Ljguo\OpenAI\Resources;

use Ljguo\OpenAI\Client;
use Ljguo\OpenAI\Exceptions\ApiException;
use Ljguo\OpenAI\Responses\ModelListResponse;
use Ljguo\OpenAI\Responses\ModelResponse;

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
