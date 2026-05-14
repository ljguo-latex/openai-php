<?php

declare(strict_types=1);

namespace OpenAI\Tests;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use OpenAI\Client;
use OpenAI\Exceptions\ApiException;
use OpenAI\Responses\UsageResponse;
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

    private function chatPayload(string $content = 'Hello!'): array
    {
        return [
            'id'      => 'chatcmpl-123',
            'object'  => 'chat.completion',
            'created' => 1677858242,
            'model'   => 'gpt-4o',
            'choices' => [
                [
                    'index'         => 0,
                    'message'       => ['role' => 'assistant', 'content' => $content],
                    'finish_reason' => 'stop',
                ],
            ],
            'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 5, 'total_tokens' => 15],
        ];
    }

    public function test_chat_send_returns_chat_response(): void
    {
        $client = $this->makeClient([
            new Response(200, [], json_encode($this->chatPayload())),
        ]);

        $response = $client->chat()
            ->model('gpt-4o')
            ->system('You are helpful.')
            ->message('Hi')
            ->send();

        $this->assertSame('Hello!', $response->content());
        $this->assertSame('stop', $response->finishReason());
        $this->assertSame(15, $response->usage->totalTokens);
    }

    public function test_chat_chained_options(): void
    {
        $client = $this->makeClient([
            new Response(200, [], json_encode($this->chatPayload('World!'))),
        ]);

        $response = $client->chat()
            ->temperature(0.5)
            ->maxTokens(100)
            ->option('top_p', 0.9)
            ->message('Hello')
            ->send();

        $this->assertSame('World!', $response->content());
    }

    public function test_chat_with_messages(): void
    {
        $client = $this->makeClient([
            new Response(200, [], json_encode($this->chatPayload('Answer'))),
        ]);

        $response = $client->chat()
            ->withMessages([
                ['role' => 'system',    'content' => 'Be brief.'],
                ['role' => 'user',      'content' => 'Question'],
                ['role' => 'assistant', 'content' => 'OK'],
                ['role' => 'user',      'content' => 'Next'],
            ])
            ->send();

        $this->assertSame('Answer', $response->content());
    }

    public function test_default_model_is_used_when_not_set(): void
    {
        $mock  = new MockHandler([new Response(200, [], json_encode($this->chatPayload()))]);
        $stack = HandlerStack::create($mock);

        $client = new Client(
            apiKey: 'test-key',
            defaultModel: 'gpt-3.5-turbo',
            httpOptions: ['handler' => $stack]
        );

        $this->assertSame('gpt-3.5-turbo', $client->getDefaultModel());
        $response = $client->chat()->message('Hi')->send();
        $this->assertSame('Hello!', $response->content());
    }

    public function test_chat_stream_calls_callback_with_chunks(): void
    {
        $sse = implode("\n", [
            'data: ' . json_encode(['choices' => [['delta' => ['content' => 'Hello']]]]),
            'data: ' . json_encode(['choices' => [['delta' => ['content' => ' World']]]]),
            'data: [DONE]',
        ]);

        $client = $this->makeClient([
            new Response(200, ['Content-Type' => 'text/event-stream'], $sse),
        ]);

        $chunks = [];
        $full = $client->chat()
            ->model('gpt-4o')
            ->message('Hi')
            ->stream(function (string $chunk) use (&$chunks) {
                $chunks[] = $chunk;
            });

        $this->assertSame(['Hello', ' World'], $chunks);
        $this->assertSame('Hello World', $full);
    }

    public function test_chat_stream_includes_usage_and_passes_usage_to_callback(): void
    {
        $sse = implode("\n", [
            'data: ' . json_encode(['choices' => [['delta' => ['content' => 'Hello']]], 'usage' => null]),
            'data: ' . json_encode([
                'choices' => [],
                'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 5, 'total_tokens' => 15],
            ]),
            'data: [DONE]',
        ]);

        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'text/event-stream'], $sse),
        ]);
        $history = [];
        $stack = HandlerStack::create($mock);
        $stack->push(\GuzzleHttp\Middleware::history($history));

        $client = new Client(
            apiKey: 'test-key',
            httpOptions: ['handler' => $stack]
        );

        $usage = null;
        $full = $client->chat()
            ->model('gpt-4o')
            ->message('Hi')
            ->stream(function (string $chunk, ?UsageResponse $chunkUsage = null) use (&$usage) {
                if ($chunkUsage !== null) {
                    $usage = $chunkUsage;
                }
            });

        $requestBody = json_decode((string) $history[0]['request']->getBody(), true);

        $this->assertSame('Hello', $full);
        $this->assertSame(['include_usage' => true], $requestBody['stream_options']);
        $this->assertInstanceOf(UsageResponse::class, $usage);
        $this->assertSame(15, $usage->totalTokens);
    }

    public function test_embeddings_send_returns_embedding_response(): void
    {
        $payload = [
            'object' => 'list',
            'model'  => 'text-embedding-3-small',
            'data'   => [
                ['index' => 0, 'object' => 'embedding', 'embedding' => [0.1, 0.2, 0.3]],
            ],
            'usage' => ['prompt_tokens' => 5, 'total_tokens' => 5],
        ];

        $client = $this->makeClient([new Response(200, [], json_encode($payload))]);

        $response = $client->embeddings()
            ->model('text-embedding-3-small')
            ->input('Hello world')
            ->send();

        $this->assertSame([0.1, 0.2, 0.3], $response->embedding());
    }

    public function test_models_list_returns_model_ids(): void
    {
        $payload = [
            'object' => 'list',
            'data'   => [
                ['id' => 'gpt-4o',        'object' => 'model', 'created' => 1000, 'owned_by' => 'openai'],
                ['id' => 'gpt-3.5-turbo', 'object' => 'model', 'created' => 1001, 'owned_by' => 'openai'],
            ],
        ];

        $client   = $this->makeClient([new Response(200, [], json_encode($payload))]);
        $response = $client->models()->list();

        $this->assertSame(['gpt-4o', 'gpt-3.5-turbo'], $response->ids());
    }

    public function test_api_exception_on_error_response(): void
    {
        $this->expectException(ApiException::class);

        $error  = ['error' => ['message' => 'Invalid API key', 'type' => 'invalid_request_error']];
        $client = $this->makeClient([new Response(401, [], json_encode($error))]);

        $client->chat()->message('Hi')->send();
    }
}
