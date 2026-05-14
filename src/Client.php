<?php

declare(strict_types=1);

namespace OpenAI;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use OpenAI\Exceptions\ApiException;
use OpenAI\Resources\Chat;
use OpenAI\Resources\Completions;
use OpenAI\Resources\Embeddings;
use OpenAI\Resources\Models;
use OpenAI\Responses\UsageResponse;

class Client
{
    private HttpClient $http;
    private string $baseUrl;
    private string $apiKey;
    private ?string $defaultModel;
    private int $timeout;

    public function __construct(
        string $apiKey,
        string $baseUrl = 'https://api.openai.com/v1',
        ?string $defaultModel = null,
        int $timeout = 30,
        array $httpOptions = []
    ) {
        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->defaultModel = $defaultModel;
        $this->timeout = $timeout;

        $this->http = new HttpClient(array_merge([
            'base_uri' => $this->baseUrl . '/',
            'timeout'  => $this->timeout,
            'headers'  => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ],
        ], $httpOptions));
    }

    public function chat(): Chat
    {
        return new Chat($this);
    }

    public function completions(): Completions
    {
        return new Completions($this);
    }

    public function embeddings(): Embeddings
    {
        return new Embeddings($this);
    }

    public function models(): Models
    {
        return new Models($this);
    }

    public function getDefaultModel(): ?string
    {
        return $this->defaultModel;
    }

    /**
     * @throws ApiException
     */
    public function post(string $uri, array $data = []): array
    {
        return $this->request('POST', $uri, ['json' => $data]);
    }

    /**
     * @throws ApiException
     */
    public function get(string $uri, array $query = []): array
    {
        return $this->request('GET', $uri, ['query' => $query]);
    }

    /**
     * @throws ApiException
     */
    public function stream(string $uri, array $data, callable $callback): void
    {
        try {
            $data['stream'] = true;
            $data['stream_options'] = array_merge(
                $data['stream_options'] ?? [],
                ['include_usage' => true]
            );

            $response = $this->http->request('POST', ltrim($uri, '/'), [
                'json'   => $data,
                'stream' => true,
            ]);

            $body = $response->getBody();

            while (!$body->eof()) {
                $line = trim($this->readLine($body));

                if ($line === '' || $line === 'data: [DONE]') {
                    continue;
                }

                if (str_starts_with($line, 'data: ')) {
                    $json = json_decode(substr($line, 6), true);

                    if (isset($json['usage']) && is_array($json['usage'])) {
                        $callback('', UsageResponse::fromArray($json['usage']));
                        continue;
                    }

                    $chunk = $json['choices'][0]['delta']['content'] ?? $json['choices'][0]['text'] ?? null;

                    if ($chunk !== null) {
                        $callback($chunk, null);
                    }
                }
            }
        } catch (GuzzleException $e) {
            $statusCode  = 0;
            $responseBody = null;

            if (method_exists($e, 'getResponse') && $e->getResponse() !== null) {
                $statusCode   = $e->getResponse()->getStatusCode();
                $responseBody = json_decode((string) $e->getResponse()->getBody(), true);
            }

            throw new ApiException(
                $responseBody['error']['message'] ?? $e->getMessage(),
                $statusCode,
                $e
            );
        }
    }

    private function readLine(\Psr\Http\Message\StreamInterface $stream): string
    {
        $line = '';

        while (!$stream->eof()) {
            $char = $stream->read(1);
            if ($char === "\n") {
                break;
            }
            $line .= $char;
        }

        return $line;
    }

    /**
     * @throws ApiException
     */
    private function request(string $method, string $uri, array $options = []): array
    {
        try {
            $response = $this->http->request($method, ltrim($uri, '/'), $options);
            $body = (string) $response->getBody();
            $decoded = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new ApiException('Failed to decode JSON response: ' . json_last_error_msg());
            }

            return $decoded;
        } catch (GuzzleException $e) {
            $statusCode = 0;
            $responseBody = null;

            if (method_exists($e, 'getResponse') && $e->getResponse() !== null) {
                $statusCode = $e->getResponse()->getStatusCode();
                $responseBody = json_decode((string) $e->getResponse()->getBody(), true);
            }

            throw new ApiException(
                $responseBody['error']['message'] ?? $e->getMessage(),
                $statusCode,
                $e
            );
        }
    }
}
