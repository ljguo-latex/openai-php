<?php

declare(strict_types=1);

namespace OpenAI\Tests;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use OpenAI\Client;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    private function makeClient(array $responses): Client
    {
        $mock  = new MockHandler($responses);
        $stack = HandlerStack::create($mock);

        return new Client(
            apiKey: 'test-key',
            httpOptions: ['handler' => $stack]
        );
    }

    public function test_chat_create_returns_chat_response(): void
    {
        $payload = [
            'id'      => 'chatcmpl-123',
            'object'  => 'chat.completion',
            'created' => 1677858242,
            'model'   => 'gpt-4o',
            'choices' => [
                [
                    'index'         => 0,
                    'message'       => ['role' => 'assistant', 'content' => 'Hello!'],
                    'finish_reason' => 'stop',
                ],
            ],
            'usage'   => [
                'prompt_tokens'     => 10,
                'completion_tokens' => 5,
                'total_tokens'      => 15,
            ],
        ];

        $client = $this->makeClient([
            new Response(200, [], json_encode($payload)),
        ]);

        $response = $client->chat()->create([
            ['role' => 'user', 'content' => 'Hi'],
        ]);

        $this->assertSame('Hello!', $response->content());
        $this->assertSame('stop', $response->finishReason());
        $this->assertSame(15, $response->usage->totalTokens);
    }

    public function test_chat_message_convenience_method(): void
    {
        $payload = [
            'id'      => 'chatcmpl-456',
            'object'  => 'chat.completion',
            'created' => 1677858300,
            'model'   => 'gpt-4o',
            'choices' => [
                [
                    'index'         => 0,
                    'message'       => ['role' => 'assistant', 'content' => 'World!'],
                    'finish_reason' => 'stop',
                ],
            ],
            'usage'   => ['prompt_tokens' => 5, 'completion_tokens' => 3, 'total_tokens' => 8],
        ];

        $client   = $this->makeClient([new Response(200, [], json_encode($payload))]);
        $response = $client->chat()->message('Hello');

        $this->assertSame('World!', $response->content());
    }

    public function test_default_model_is_passed_to_request(): void
    {
        $payload = [
            'id'      => 'chatcmpl-789',
            'object'  => 'chat.completion',
            'created' => 1677858400,
            'model'   => 'gpt-3.5-turbo',
            'choices' => [
                [
                    'index'         => 0,
                    'message'       => ['role' => 'assistant', 'content' => 'Hi!'],
                    'finish_reason' => 'stop',
                ],
            ],
            'usage'   => ['prompt_tokens' => 5, 'completion_tokens' => 2, 'total_tokens' => 7],
        ];

        $mock  = new MockHandler([new Response(200, [], json_encode($payload))]);
        $stack = HandlerStack::create($mock);

        $client = new Client(
            apiKey: 'test-key',
            defaultModel: 'gpt-3.5-turbo',
            httpOptions: ['handler' => $stack]
        );

        $this->assertSame('gpt-3.5-turbo', $client->getDefaultModel());

        $response = $client->chat()->message('Hi');
        $this->assertSame('Hi!', $response->content());
    }

    public function test_embeddings_create_returns_embedding_response(): void
    {
        $payload = [
            'object' => 'list',
            'model'  => 'text-embedding-3-small',
            'data'   => [
                ['index' => 0, 'object' => 'embedding', 'embedding' => [0.1, 0.2, 0.3]],
            ],
            'usage'  => ['prompt_tokens' => 5, 'total_tokens' => 5],
        ];

        $client   = $this->makeClient([new Response(200, [], json_encode($payload))]);
        $response = $client->embeddings()->create('Hello world');

        $this->assertSame([0.1, 0.2, 0.3], $response->embedding());
    }

    public function test_models_list_returns_model_ids(): void
    {
        $payload = [
            'object' => 'list',
            'data'   => [
                ['id' => 'gpt-4o', 'object' => 'model', 'created' => 1000, 'owned_by' => 'openai'],
                ['id' => 'gpt-3.5-turbo', 'object' => 'model', 'created' => 1001, 'owned_by' => 'openai'],
            ],
        ];

        $client   = $this->makeClient([new Response(200, [], json_encode($payload))]);
        $response = $client->models()->list();

        $this->assertSame(['gpt-4o', 'gpt-3.5-turbo'], $response->ids());
    }

    public function test_api_exception_on_error_response(): void
    {
        $this->expectException(\OpenAI\Exceptions\ApiException::class);

        $error  = ['error' => ['message' => 'Invalid API key', 'type' => 'invalid_request_error']];
        $client = $this->makeClient([new Response(401, [], json_encode($error))]);

        $client->chat()->message('Hi');
    }
}
