# openai-php

A lightweight, fluent PHP client for the OpenAI API. Supports custom base URL, API key, and default model — making it easy to use with any OpenAI-compatible endpoint (OpenAI, Azure OpenAI, local models, etc.).

## Requirements

- PHP 8.1+
- Composer

## Installation

```bash
composer require ljguo/openai-php
```

## Quick Start

```php
use Ljguo\OpenAI\OpenAI;

$client = OpenAI::client(
    apiKey: 'sk-...',
    baseUrl: 'https://api.openai.com/v1', // optional, default
    defaultModel: 'gpt-4o',               // optional
);

// Chat completion
$response = $client->chat()->message('What is the capital of France?');
echo $response->content(); // "Paris"
```

## Usage

### Chat Completions

```php
// Single message (convenience)
$response = $client->chat()->message('Hello!');
echo $response->content();

// Full message array
$response = $client->chat()->create(
    messages: [
        ['role' => 'system', 'content' => 'You are a helpful assistant.'],
        ['role' => 'user',   'content' => 'What is PHP?'],
    ],
    model:       'gpt-4o',    // override default model
    temperature: 0.7,
    maxTokens:   512,
    options:     ['top_p' => 0.9],
);

echo $response->content();
echo $response->finishReason(); // "stop"
echo $response->usage->totalTokens;
```

### Text Completions

```php
$response = $client->completions()->create(
    prompt:      'Once upon a time',
    model:       'gpt-3.5-turbo-instruct',
    temperature: 0.8,
    maxTokens:   100,
);

echo $response->text();
```

### Embeddings

```php
// Single input
$response = $client->embeddings()->create('Hello, world!');
$vector = $response->embedding(); // float[]

// Multiple inputs
$response = $client->embeddings()->create(['Hello', 'World']);
foreach ($response->data as $item) {
    echo "Index {$item->index}: " . count($item->embedding) . " dimensions\n";
}
```

### Models

```php
// List all available models
$list = $client->models()->list();
print_r($list->ids()); // ['gpt-4o', 'gpt-3.5-turbo', ...]

// Retrieve a specific model
$model = $client->models()->retrieve('gpt-4o');
echo $model->id;
echo $model->ownedBy;
```

## Custom / Compatible Endpoints

Point the client at any OpenAI-compatible API (e.g., a local Ollama server, Azure OpenAI, or a proxy):

```php
$client = OpenAI::client(
    apiKey:  'local-key',
    baseUrl: 'http://localhost:11434/v1',
    defaultModel: 'llama3',
);
```

## Error Handling

```php
use Ljguo\OpenAI\Exceptions\ApiException;

try {
    $response = $client->chat()->message('Hello');
} catch (ApiException $e) {
    echo $e->getMessage();       // API error message
    echo $e->getStatusCode();    // HTTP status code

    if ($e->isRateLimitError()) {
        // handle 429
    } elseif ($e->isAuthenticationError()) {
        // handle 401
    } elseif ($e->isServerError()) {
        // handle 5xx
    }
}
```

## Direct Client Construction

```php
use Ljguo\OpenAI\Client;

$client = new Client(
    apiKey:       'sk-...',
    baseUrl:      'https://api.openai.com/v1',
    defaultModel: 'gpt-4o',
    timeout:      60,
    httpOptions:  [], // Guzzle options
);
```

## Running Tests

```bash
composer install
composer test
```

## License

MIT
