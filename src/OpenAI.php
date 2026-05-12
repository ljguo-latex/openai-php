<?php

declare(strict_types=1);

namespace Ljguo\OpenAI;

class OpenAI
{
    public static function client(
        string $apiKey,
        string $baseUrl = 'https://api.openai.com/v1',
        ?string $defaultModel = null,
        int $timeout = 30,
        array $httpOptions = []
    ): Client {
        return new Client($apiKey, $baseUrl, $defaultModel, $timeout, $httpOptions);
    }
}
