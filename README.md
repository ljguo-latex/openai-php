# openai-php

适用于 OpenAI API 的轻量级 PHP 客户端，支持自定义接口地址、API Key 和默认模型，可无缝对接 OpenAI、Azure OpenAI、本地模型（如 Ollama）等任何兼容端点。

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

// 发送一条聊天消息
$response = $client->chat()->message('法国的首都是哪里？');
echo $response->content(); // "巴黎"
```

## 使用说明

### 对话补全（Chat Completions）

```php
// 单条消息（便捷方法）
$response = $client->chat()->message('你好！');
echo $response->content();

// 完整消息数组
$response = $client->chat()->create(
    messages: [
        ['role' => 'system', 'content' => '你是一个有用的助手。'],
        ['role' => 'user',   'content' => 'PHP 是什么？'],
    ],
    model:       'gpt-4o',  // 覆盖默认模型
    temperature: 0.7,
    maxTokens:   512,
    options:     ['top_p' => 0.9],
);

echo $response->content();
echo $response->finishReason(); // "stop"
echo $response->usage->totalTokens;
```

### 文本补全（Completions）

```php
$response = $client->completions()->create(
    prompt:      '从前有座山',
    model:       'gpt-3.5-turbo-instruct',
    temperature: 0.8,
    maxTokens:   100,
);

echo $response->text();
```

### 向量嵌入（Embeddings）

```php
// 单条输入
$response = $client->embeddings()->create('你好，世界！');
$vector = $response->embedding(); // float[]

// 多条输入
$response = $client->embeddings()->create(['Hello', 'World']);
foreach ($response->data as $item) {
    echo "第 {$item->index} 条：" . count($item->embedding) . " 维\n";
}
```

### 模型列表（Models）

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

只需修改 `baseUrl` 即可对接任何 OpenAI 兼容的 API，例如本地 Ollama、Azure OpenAI 或代理服务：

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
    $response = $client->chat()->message('你好');
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
