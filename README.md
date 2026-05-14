# openai-php

适用于 OpenAI API 的轻量级 PHP 客户端，支持链式调用、流式输出，以及自定义接口地址、API Key 和默认模型。可无缝对接 OpenAI、Azure OpenAI、本地模型（如 Ollama）等任何兼容端点。

## 环境要求

- PHP 8.1+
- Composer

## 安装

```bash
composer require ljguo-latex/openai-php
```

## 快速开始

```php
use OpenAI\OpenAI;

$client = OpenAI::client(
    apiKey: 'sk-...',
    baseUrl: 'https://api.openai.com/v1', // 可选，默认值
    defaultModel: 'gpt-4o',               // 可选
);

$response = $client->chat()
    ->message('法国的首都是哪里？')
    ->send();

echo $response->content(); // "巴黎"
```

## 对话补全（Chat）

### 普通请求

```php
$response = $client->chat()
    ->model('gpt-4o')
    ->system('你是一个有用的助手。')
    ->message('PHP 是什么？')
    ->temperature(0.7)
    ->maxTokens(512)
    ->send();

echo $response->content();
echo $response->finishReason(); // "stop"
echo $response->usage->totalTokens;
```

### 多轮对话

```php
$response = $client->chat()
    ->system('你是一个有用的助手。')
    ->user('我叫小明。')
    ->assistant('你好，小明！')
    ->user('我叫什么名字？')
    ->send();

echo $response->content(); // "你叫小明。"
```

### 流式输出

```php
$client->chat()
    ->model('gpt-4o')
    ->message('写一首关于春天的诗')
    ->stream(function (string $chunk) {
        echo $chunk;
        ob_flush();
        flush();
    });
```

### 流式输出获取 token 用量

```php
use OpenAI\Responses\UsageResponse;

$client->chat()
    ->model('gpt-4o')
    ->message('写一首关于春天的诗')
    ->stream(function (string $chunk, ?UsageResponse $usage = null) {
        echo $chunk;

        if ($usage !== null) {
            echo "\nPrompt tokens: " . $usage->promptTokens;
            echo "\nCompletion tokens: " . $usage->completionTokens;
            echo "\nTotal tokens: " . $usage->totalTokens;
        }
    });
```

### 其他链式方法

| 方法 | 说明 |
|------|------|
| `model(string)` | 指定模型 |
| `system(string)` | 添加 system 消息 |
| `user(string)` | 添加 user 消息 |
| `assistant(string)` | 添加 assistant 消息 |
| `message(string)` | `user()` 的别名 |
| `withMessages(array)` | 批量设置消息数组 |
| `temperature(float)` | 采样温度，默认 `1.0` |
| `maxTokens(int)` | 最大 token 数 |
| `option(string, mixed)` | 设置任意额外参数 |

## 文本补全（Completions）

```php
// 普通请求
$response = $client->completions()
    ->model('gpt-3.5-turbo-instruct')
    ->prompt('从前有座山')
    ->temperature(0.8)
    ->maxTokens(100)
    ->send();

echo $response->text();

// 流式输出
$client->completions()
    ->prompt('从前有座山')
    ->stream(function (string $chunk, ?\OpenAI\Responses\UsageResponse $usage = null) {
        echo $chunk;

        if ($usage !== null) {
            echo "\nTotal tokens: " . $usage->totalTokens;
        }
    });
```

## 向量嵌入（Embeddings）

```php
// 单条输入
$response = $client->embeddings()
    ->model('text-embedding-3-small')
    ->input('你好，世界！')
    ->send();

$vector = $response->embedding(); // float[]

// 多条输入
$response = $client->embeddings()
    ->input(['Hello', 'World'])
    ->send();

foreach ($response->data as $item) {
    echo "第 {$item->index} 条：" . count($item->embedding) . " 维\n";
}
```

## 模型列表（Models）

```php
// 列出所有可用模型
$list = $client->models()->list();
print_r($list->ids()); // ['gpt-4o', 'gpt-3.5-turbo', ...]

// 获取指定模型详情
$model = $client->models()->retrieve('gpt-4o');
echo $model->id;
echo $model->ownedBy;
```

## 自定义 / 兼容端点

只需修改 `baseUrl` 即可对接任何 OpenAI 兼容 API：

```php
$client = OpenAI::client(
    apiKey:       'local-key',
    baseUrl:      'http://localhost:11434/v1',
    defaultModel: 'llama3',
);
```

## 错误处理

```php
use OpenAI\Exceptions\ApiException;

try {
    $response = $client->chat()->message('你好')->send();
} catch (ApiException $e) {
    echo $e->getMessage();    // API 错误信息
    echo $e->getStatusCode(); // HTTP 状态码

    if ($e->isRateLimitError()) {
        // 处理 429 限流
    } elseif ($e->isAuthenticationError()) {
        // 处理 401 认证失败
    } elseif ($e->isServerError()) {
        // 处理 5xx 服务端错误
    }
}
```

## 直接实例化客户端

```php
use OpenAI\Client;

$client = new Client(
    apiKey:       'sk-...',
    baseUrl:      'https://api.openai.com/v1',
    defaultModel: 'gpt-4o',
    timeout:      60,
    httpOptions:  [], // Guzzle 配置项
);
```

## 运行测试

```bash
composer install
composer test
```

## 开源协议

MIT
